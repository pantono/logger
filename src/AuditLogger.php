<?php

namespace Pantono\Logger;

use Pantono\Logger\Repository\AuditLogRepository;
use Pantono\Hydrator\Hydrator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Pantono\Logger\Model\AuditLog;
use Pantono\Logger\Event\PreAuditLogSaveEvent;
use Pantono\Logger\Event\PostAuditLogSaveEvent;
use Pantono\Logger\Filter\AuditLogFilter;
use Pantono\Contracts\Security\SecurityContextInterface;
use Pantono\Contracts\Locator\UserInterface;
use Pantono\Contracts\Application\Proxy\ProxyInterface;

class AuditLogger
{
    private AuditLogRepository $repository;
    private Hydrator $hydrator;
    private EventDispatcher $dispatcher;
    private SecurityContextInterface $securityContext;

    public function __construct(AuditLogRepository $repository, Hydrator $hydrator, EventDispatcher $dispatcher, SecurityContextInterface $securityContext)
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->dispatcher = $dispatcher;
        $this->securityContext = $securityContext;
    }

    private function getAuditLogById(int $id): ?AuditLog
    {
        return $this->hydrator->hydrate(AuditLog::class, $this->repository->getLogById($id));
    }

    public function addLogForModel(string $modelClass, string $modelId, string $entry, ?array $previousState = null, ?array $newState = null): AuditLog
    {
        $log = new AuditLog();
        $log->setDate(new \DateTimeImmutable());
        $log->setModel($modelClass);
        $log->setModelId($modelId);
        $log->setEntry($entry);
        $log->setPreviousState($previousState);
        $log->setNewState($newState);
        if ($this->securityContext->has('user')) {
            /**
             * @var UserInterface $user
             */
            $user = $this->securityContext->get('user');
            $log->setUserId($user->getId());
            $log->setUserName($user->getName());
        } else {
            $log->setUserId(1);
            $log->setUserName('Unknown User');
        }

        $this->saveAuditLog($log);
        return $log;
    }

    public function autoLog(mixed $currentModel, mixed $previousModel = null): ?AuditLog
    {
        // Handle deletes
        if ($currentModel === null && $previousModel !== null) {
            $modelClass = $this->getModelName($previousModel);
            $modelId = $this->getModelId($previousModel);
            $prev = $this->normalizeModel($previousModel);
            return $this->addLogForModel($modelClass, $modelId, 'Deleted', $prev, null);
        }

        // Handle creates
        if ($currentModel !== null && $previousModel === null) {
            $modelClass = $this->getModelName($currentModel);
            $modelId = $this->getModelId($currentModel);
            $new = $this->normalizeModel($currentModel);
            return $this->addLogForModel($modelClass, $modelId, 'Created', null, $new);
        }

        // Nothing to do
        if ($currentModel === null && $previousModel === null) {
            return null;
        }

        // Both present: update
        if (get_class($currentModel) !== get_class($previousModel)) {
            throw new \InvalidArgumentException('autoLog requires both models to be of the same type');
        }

        $current = $this->normalizeModel($currentModel);
        $previous = $this->normalizeModel($previousModel);

        [$prevDiff, $newDiff] = $this->diffStates($previous, $current);

        // If no differences, do not create a log entry
        if (empty($prevDiff) && empty($newDiff)) {
            return null;
        }

        $modelClass = get_class($currentModel);
        $modelId = $this->getModelId($currentModel);

        return $this->addLogForModel($modelClass, $modelId, 'Updated', $prevDiff, $newDiff);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeModel(object $model): array
    {
        // Prefer toArray
        if (method_exists($model, 'toArray')) {
            $data = $model->toArray();
            if (is_array($data)) {
                return $this->sanitizeValues($data);
            }
        }

        // JsonSerializable support
        if ($model instanceof \JsonSerializable) {
            $data = $model->jsonSerialize();
            if (is_array($data)) {
                return $this->sanitizeValues($data);
            }
        }

        // Collect via getters
        $result = [];
        foreach (get_class_methods($model) as $method) {
            if (str_starts_with($method, 'get') && (new \ReflectionMethod($model, $method))->getNumberOfRequiredParameters() === 0) {
                $key = lcfirst(substr($method, 3));
                try {
                    $value = $model->{$method}();
                } catch (\Throwable $e) {
                    continue;
                }
                $result[$key] = $value;
            }
            if (str_starts_with($method, 'is') && (new \ReflectionMethod($model, $method))->getNumberOfRequiredParameters() === 0) {
                $key = lcfirst(substr($method, 2));
                try {
                    $value = $model->{$method}();
                } catch (\Throwable $e) {
                    continue;
                }
                $result[$key] = $value;
            }
        }

        // Fallback to public properties
        $result = array_merge($result, get_object_vars($model));

        return $this->sanitizeValues($result);
    }

    /**
     * Ensure values are serializable and comparable
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function sanitizeValues(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if ($v instanceof \DateTimeInterface) {
                $out[$k] = $v->format(DATE_ATOM);
            } elseif (is_object($v)) {
                $out[$k] = method_exists($v, '__toString') ? (string)$v : json_decode(json_encode($v, JSON_PARTIAL_OUTPUT_ON_ERROR), true);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $old
     * @param array<string, mixed> $new
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    private function diffStates(array $old, array $new): array
    {
        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $prev = [];
        $curr = [];
        foreach ($keys as $key) {
            $ov = $old[$key] ?? null;
            $nv = $new[$key] ?? null;
            if ($this->valuesDiffer($ov, $nv)) {
                $prev[$key] = $ov;
                $curr[$key] = $nv;
            }
        }
        return [$prev, $curr];
    }

    private function valuesDiffer(mixed $a, mixed $b): bool
    {
        if (is_array($a) || is_array($b)) {
            return json_encode($a) !== json_encode($b);
        }
        return $a !== $b;
    }

    private function getModelId(object $model): string
    {
        // Common conventions: getId or id property
        if (method_exists($model, 'getId')) {
            $id = $model->getId();
        } elseif (property_exists($model, 'id')) {
            $id = $model->id;
        } else {
            throw new \InvalidArgumentException('autoLog requires the model to expose an identifier via getId() or id property');
        }

        if ($id === null) {
            throw new \InvalidArgumentException('autoLog requires a non-null model identifier');
        }

        return (string)$id;
    }

    /**
     * @return AuditLog[]
     */
    public function getAuditLogByFilter(AuditLogFilter $filter): array
    {
        return $this->hydrator->hydrateSet(AuditLog::class, $this->repository->getAuditLogByFilter($filter));
    }

    public function saveAuditLog(AuditLog $log): void
    {
        $previous = $log->getId() ? $this->getAuditLogById($log->getId()) : null;
        $event = new PreAuditLogSaveEvent();
        $event->setCurrent($log);
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);

        $this->repository->saveLog($log);

        $event = new PostAuditLogSaveEvent();
        $event->setCurrent($log);
        $event->setPrevious($previous);
        $this->dispatcher->dispatch($event);
    }

    private function getModelName(object $model): string
    {
        $reflection = new \ReflectionClass($model);
        if (in_array(ProxyInterface::class, $reflection->getInterfaceNames())) {
            return $reflection->getParentClass()->getName();
        }
        return $reflection->getName();
    }
}
