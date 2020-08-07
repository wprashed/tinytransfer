<?php

namespace system\db\Json;

use Closure;

// PipeInterface
interface PipeInterface
{
    public function process(array $data);
}

// FilterPipe
class FilterPipe implements PipeInterface
{
    protected $filters = [];

    public function process(array $data)
    {
        $filters = $this->filters;
        return array_filter($data, function ($row) use ($filters) {
            $result = true;
            foreach ($filters as $i => $filter) {
                list($filter, $type) = $filter;
                switch ($type) {
                    case 'and':
                        $result = ($result and $filter($row));
                        break;
                    case 'or':
                        $result = ($result or $filter($row));
                        break;
                    default:
                        throw new \Exception("Filter type must be 'AND' or 'OR'.", 1);
                }
            }
            return $result;
        });
    }

    public function add(Closure $filter, $type = 'AND')
    {
        $this->filters[] = [$filter, strtolower($type)];
    }
}

// LimiterPipe
class LimiterPipe implements PipeInterface
{
    protected $limit;
    protected $offset;

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset($offset)
    {
        if (!is_null($offset)) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function process(array $data)
    {
        $limit = (int) $this->limit ?: count($data);
        $offset = (int) $this->offset;
        return array_slice($data, $offset, $limit);
    }
}

// MapperPipe
class MapperPipe implements PipeInterface
{
    protected $mappers = [];

    public function process(array $data)
    {
        foreach ($this->mappers as $mapper) {
            $data = array_map($mapper, $data);
        }

        return $data;
    }

    public function add(Closure $mapper)
    {
        $this->mappers[] = $mapper;
    }
}

// SorterPipe
class SorterPipe implements PipeInterface
{
    protected $value;
    protected $ascending;

    public function __construct(Closure $value, $ascending = 'asc')
    {
        $this->value = $value;
        $this->ascending = strtolower($ascending);
    }

    public function process(array $data)
    {
        return $this->sort($data, $this->value, $this->ascending);
    }

    public function sort($array, $value, $ascending)
    {
        $values = array_map(function ($row) use ($value) {
            return $value($row);
        }, $array);

        switch ($ascending) {
            case 'asc':asort($values);
                break;
            case 'desc':arsort($values);
                break;
        }

        $keys = array_keys($values);

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $array[$key];
        }
        return $result;
    }
}

class Query
{
    const TYPE_GET = 'get';
    const TYPE_INSERT = 'insert';
    const TYPE_UPDATE = 'update';
    const TYPE_DELETE = 'delete';
    const TYPE_SAVE = 'save';

    protected $collection;

    protected $pipes = [];

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function where($filter)
    {
        $args = func_get_args();
        array_unshift($args, 'AND');
        call_user_func_array([$this, 'addWhere'], $args);
        return $this;
    }

    public function orWhere($filter)
    {
        $args = func_get_args();
        array_unshift($args, 'OR');
        call_user_func_array([$this, 'addWhere'], $args);
        return $this;
    }

    public function map(Closure $mapper)
    {
        $this->addMapper($mapper);
        return $this;
    }

    public function select(array $columns)
    {
        $resolvedColumns = [];
        foreach ($columns as $column) {
            $exp = explode(':', $column);
            $col = $exp[0];
            if (count($exp) > 1) {
                $keyAlias = $exp[1];
            } else {
                $keyAlias = $exp[0];
            }
            $resolvedColumns[$col] = $keyAlias;
        }

        $keyAliases = array_values($resolvedColumns);

        return $this->map(function ($row) use ($resolvedColumns, $keyAliases) {
            foreach ($resolvedColumns as $col => $keyAlias) {
                if (!isset($row[$keyAlias])) {
                    $row[$keyAlias] = $row[$col];
                }
            }

            foreach ($row->toArray() as $col => $value) {
                if (!in_array($col, $keyAliases)) {
                    unset($row[$col]);
                }
            }

            return $row;
        });
    }

    public function withOne($relation, $as, $otherKey, $operator = '=', $thisKey = '_id')
    {
        if (false == $relation instanceof Query and false == $relation instanceof Collection) {
            throw new \Exception("Relation must be instanceof Query or Collection", 1);
        }
        return $this->map(function ($row) use ($relation, $as, $otherKey, $operator, $thisKey) {
            $otherData = $relation->where($otherKey, $operator, $row[$thisKey])->first();
            $row[$as] = $otherData;
            return $row;
        });
    }

    public function withMany($relation, $as, $otherKey, $operator = '=', $thisKey = '_id')
    {
        if (false !== $relation instanceof Query and false == $relation instanceof Collection) {
            throw new \Exception("Relation must be instanceof Query or Collection", 1);
        }
        return $this->map(function ($row) use ($relation, $as, $otherKey, $operator, $thisKey) {
            $otherData = $relation->where($otherKey, $operator, $row[$thisKey])->get();
            $row[$as] = $otherData;
            return $row;
        });
    }

    public function sortBy($key, $asc = 'asc')
    {
        $asc = strtolower($asc);
        if (!in_array($asc, ['asc', 'desc'])) {
            throw new \Exception("Ascending must be 'asc' or 'desc'", 1);
        }

        if ($key instanceof Closure) {
            $value = $key;
        } else {
            $value = function ($row) use ($key) {
                return $row[$key];
            };
        }

        $this->addSorter(function ($row) use ($value) {
            return $value(new \system\db\Json\ArrayExtra($row));
        }, $asc);
        return $this;
    }

    public function skip($offset)
    {
        $this->getLimiter()->setOffset($offset);
        return $this;
    }

    public function take($limit, $offset = null)
    {
        $this->getLimiter()->setLimit($limit)->setOffset($offset);
        return $this;
    }

    public function get(array $select = [])
    {
        if (!empty($select)) {
            $this->select($select);
        }
        return $this->execute(self::TYPE_GET);
    }

    public function first(array $select = array())
    {
        $data = $this->take(1)->get($select);
        return array_shift($data);
    }

    public function update(array $new)
    {
        return $this->execute(self::TYPE_UPDATE, $new);
    }

    public function delete()
    {
        return $this->execute(self::TYPE_DELETE);
    }

    public function save()
    {
        return $this->execute(self::TYPE_SAVE);
    }

    public function count()
    {
        return count($this->get());
    }

    public function sum($key)
    {
        $sum = 0;
        foreach ($this->get() as $data) {
            $data = new \system\db\Json\ArrayExtra($data);
            $sum += $data[$key];
        }
        return $sum;
    }

    public function avg($key)
    {
        $sum = 0;
        $count = 0;
        foreach ($this->get() as $data) {
            $data = new \system\db\Json\ArrayExtra($data);
            $sum += $data[$key];
            $count++;
        }
        return $sum / $count;
    }

    public function lists($key, $resultKey = null)
    {
        $result = [];
        foreach ($this->get() as $i => $data) {
            $data = new \system\db\Json\ArrayExtra($data);
            $k = $resultKey ? $data[$resultKey] : $i;
            $result[$k] = $data[$key];
        }
        return $result;
    }

    public function pluck($key, $resultKey = null)
    {
        return $this->lists($key, $resultKey);
    }

    public function min($key)
    {
        return min($this->lists($key));
    }

    public function max($key)
    {
        return max($this->lists($key));
    }

    public function getPipes()
    {
        return $this->pipes;
    }

    protected function execute($type, $arg = null)
    {
        return $this->getCollection()->execute($this, $type, $arg);
    }

    protected function addWhere($type, $filter)
    {
        if ($filter instanceof Closure) {
            return $this->addFilter($filter, $type);
        }

        $args = func_get_args();
        $key = $args[1];
        if (count($args) > 3) {
            $operator = $args[2];
            $value = $args[3];
        } else {
            $operator = '=';
            $value = $args[2];
        }

        switch ($operator) {
            case '=':
                $filter = function ($row) use ($key, $value) {
                    return $row[$key] == $value;
                };
                break;
            case '>':
                $filter = function ($row) use ($key, $value) {
                    return $row[$key] > $value;
                };
                break;
            case '>=':
                $filter = function ($row) use ($key, $value) {
                    return $row[$key] >= $value;
                };
                break;
            case '<':
                $filter = function ($row) use ($key, $value) {
                    return $row[$key] < $value;
                };
                break;
            case '<=':
                $filter = function ($row) use ($key, $value) {
                    return $row[$key] <= $value;
                };
                break;
            case 'in':
                $filter = function ($row) use ($key, $value) {
                    return in_array($row[$key], (array) $value);
                };
                break;
            case 'not in':
                $filter = function ($row) use ($key, $value) {
                    return !in_array($row[$key], (array) $value);
                };
                break;
            case 'match':
                $filter = function ($row) use ($key, $value) {
                    return (bool) preg_match($value, $row[$key]);
                };
                break;
            case 'like':
                $filter = function ($row) use ($key, $value) {
                    if (empty($value)) {
                        return true;
                    } else {
                        return strpos($row[$key], $value) !== false ? true : false;
                    }
                };
                break;
            case 'between':
                if (!is_array($value) or count($value) < 2) {
                    throw new \Exception("Query between need exactly 2 items in array");
                }
                $filter = function ($row) use ($key, $value) {
                    $v = $row[$key];
                    return $v >= $value[0] and $v <= $value[1];
                };
                break;
        }

        if (!$filter) {
            throw new \Exception("Operator {$operator} is not available");
        }

        $this->addFilter($filter, $type);
    }

    protected function addFilter(Closure $filter, $type = 'AND')
    {
        $lastPipe = $this->getLastPipe();
        if (false == $lastPipe instanceof \system\db\Json\FilterPipe) {
            $pipe = new \system\db\Json\FilterPipe($this);
            $this->addPipe($pipe);
        } else {
            $pipe = $lastPipe;
        }

        $newFilter = function ($row) use ($filter) {
            $row = new \system\db\Json\ArrayExtra($row);
            return $filter($row);
        };

        $pipe->add($newFilter, $type);
    }

    protected function addMapper(Closure $mapper)
    {
        $lastPipe = $this->getLastPipe();
        if (false == $lastPipe instanceof MapperPipe) {
            $pipe = new MapperPipe($this);
            $this->addPipe($pipe);
        } else {
            $pipe = $lastPipe;
        }

        $keyId = $this->getCollection()->getKeyId();
        $keyOldId = $this->getCollection()->getKeyOldId();

        $newMapper = function ($row) use ($mapper, $keyId, $keyOldId) {
            $row = new \system\db\Json\ArrayExtra($row);
            $result = $mapper($row);

            if (is_array($result)) {
                $new = $result;
            } elseif ($result instanceof \system\db\Json\ArrayExtra) {
                $new = $result->toArray();
            } else {
                $new = null;
            }

            if (is_array($new) and isset($new[$keyId])) {
                if ($row[$keyId] != $new[$keyId]) {
                    $new[$keyOldId] = $row[$keyId];
                }
            }

            return $new;
        };

        $pipe->add($newMapper);
    }

    protected function addSorter(Closure $value, $asc)
    {
        $pipe = new SorterPipe($value, $asc);
        $this->addPipe($pipe);
    }

    protected function getLimiter()
    {
        $lastPipe = $this->getLastPipe();
        if (false == $lastPipe instanceof LimiterPipe) {
            $limiter = new LimiterPipe;
            $this->addPipe($limiter);
        } else {
            $limiter = $lastPipe;
        }

        return $limiter;
    }

    protected function addPipe(PipeInterface $pipe)
    {
        $this->pipes[] = $pipe;
    }

    protected function getLastPipe()
    {
        return !empty($this->pipes) ? $this->pipes[count($this->pipes) - 1] : null;
    }

    public function __call($method, $args)
    {
        $macro = $this->collection->getMacro($method);

        if ($macro) {
            return call_user_func_array($macro, array_merge([$this], $args));
        } else {
            throw new \Exception("Undefined method or macro '{$method}'.");
        }
    }
}
