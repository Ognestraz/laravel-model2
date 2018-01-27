<?php namespace Model\Traits;

use DB;
use Illuminate\Support\Facades\Log;

trait Treeable
{
    protected $tree;
    protected static $listTree = [];
    protected static $parentTree = [];
    protected $listTreeSelect = [];
    protected $simpleTree = [];
    protected static $childrenKey = 'children';

    public static function resetTree()
    {
        self::$parentTree = null;
    }

    static public function findPath($path)
    {
        return self::where('path', $path);
    }

    public function endPath()
    {
        $part = explode('/', $this->path);
        return end($part);
    }    

    protected static function treeableDefaultOrder($model)
    {
        if ([] === $model->getOriginal()) {
            $maxOrder = self::where('parent_id', 0)->max('order');
            if (null === $maxOrder) {
                $model->order = 0;
                return ;
            }
            $model->order = $maxOrder + 1;
        }        
    }

    protected static function treeableReOrderParentId($model)
    {
        $oldParentId = $model->getOriginal('parent_id');
        if (null !== $oldParentId && $model->parent_id !== $oldParentId) {
            if ($model->brothers()->count()) {
                $model->brothers()
                    ->where('order', '>=', $model->order)
                    ->update([
                        'order' => DB::raw('`order` + 1')
                    ]);
            } else {
                $model->order = 0;
            }            
            
            self::where('parent_id', (int)$oldParentId)
                    ->where('order', '>', $model->getOriginal('order'))
                    ->update([
                        'order' => DB::raw('`order` - 1')
            ]);
            return;
        }
        
        if ($model->parent_id) {
            $parent = self::find($model->parent_id);
            $order = 0;
            if (null !== $parent && 0 !== $parent->childs()->count()) {
                $order = $parent->childs()->max('order') + 1;
            }
        } else {
            $order = 0;
            if (self::where('parent_id', 0)->count()) {
                $order = self::where('parent_id', 0)->max('order') + 1;
            }
        }
        $model->order = $order;
    }    
    
    protected static function treeableReOrderOrder($model) {
        if ([] === $model->getOriginal()) {
            return ;
        }

        $oldOrder = $model->getOriginal('order') ?: 0;
        if ($model->order < $oldOrder) {
            $model->brothers()
                ->where('order', '>=', $model->order)
                ->where('order', '<=', $oldOrder)
                ->update([
                    'order' => DB::raw('`order` + 1')
                ]);
        } else {
            $model->brothers()
                ->where('order', '>', $oldOrder)
                ->where('order', '<=', $model->order)
                ->update([
                    'order' => DB::raw('`order` - 1')
                ]);
        }
    }

    protected static function treeableDefaultPath($model) {
        if (null === $model->path) {
            $model->path = $model->name;
        }
    }

    protected static function treeableRePathParentId($model) {
        $path = $model->endPath();
        if ($model->parent_id) {
            $parent = $model->getParent();
            if ($parent->path) {
                $model->path = ('/' !== $parent->path) ? $parent->path . '/' . $path : '/' . $path;
            } elseif ($path) {
                $model->path = $path;
            }

            $oldParent = self::find($model->getOriginal('parent_id'));
            $oldParentPath = '';
            if (null !== $oldParent) {
                $oldParentPath = $oldParent->path;
            }

            foreach ($model->childs()->get() as $item) {
                $item->path = substr_replace($item->path, $parent->path, 0, strlen($oldParentPath));
                Log::info('Item path', [$item->toArray(), $item->getOriginal()]);
                $item->save();
            }

        } elseif ($path) {
            $model->path = $path;                    
        }
    }
    
    protected static function treeableRePathChilds($model) {
        if ([] === $model->getOriginal()) {
            return ;
        }
        foreach ($model->childs()->get() as $item) {
            $item->path = $model->path . '/' . $item->endPath();
            $item->save();
        }
    }    

    public function scopeSort($query, $by = 'asc')
    {
        return $query->orderBy('order', $by);
    }    
    
    protected static function treeableOrderShift($model)
    {
        $i = 0;
        foreach ($model->brothers()->sort()->get() as $m) {
            $m->order = $i++;
            $m->save();
        }        
    }

    protected static function treeableEventSaving($model)
    {
        self::treeableDefaultPath($model);
        self::treeableDefaultOrder($model);

        if (true === $model->isDirty('parent_id')) {
            self::treeableReOrderParentId($model);
            self::treeableRePathParentId($model);
        } elseif (true === $model->isDirty('order')) {
            self::treeableReOrderOrder($model);
        }

        if (true === $model->isDirty('path') && true !== $model->isDirty('parent_id')) {
            self::treeableRePathChilds($model);
        }        
    }

    protected static function bootTreeable()
    {
        static::creating(function($model) {
            static::treeableEventSaving($model);
        });

        static::updating(function($model) {
            static::treeableEventSaving($model);
        });

        self::deleted(function($model) {
            static::treeableOrderShift($model);

            $model->childs()->get()->each(function($model) {
                $model->delete();
            });
        });        
    }    

    public function getParent()
    {
        return !empty($this->parent_id) ? self::find($this->parent_id) : null;
    }

    public function childs()
    {
        return self::where('parent_id', $this->id);
    }

    public function getChilds()
    {
        if (null === static::$parentTree) {
            $this->createTree(true);
        }

        $list = [];
        if (true === array_key_exists($this->id, static::$parentTree)) {
            foreach (static::$parentTree[$this->id] as $id) {
                $list[] = static::$listTree[$id];
            }
        }

        return collect($list);
    }    

    public function brothers()
    {
        return self::where('parent_id', $this->parent_id)->where('id', '!=', $this->id);
    }

    public function setParent($id)
    {
        $this->parent_id = $id;
        $this->save();
    }

    public function moveBefore($id)
    {
        $target = self::find($id);
        if (null === $target || $target->parent_id == $this->id) {
            return false;
        }

        if ($this->parent_id === $target->parent_id && $target->order >= $this->order) {
            $this->order = $target->order - 1;
        } else {
            $this->order = $target->order;
        }
        $this->parent_id = $target->parent_id;

        return $this->save();
    }

    public function moveAfter($id)
    {
        $target = self::find($id);
        if (null === $target || $target->parent_id == $this->id) {
            return false;
        }

        if ($this->parent_id === $target->parent_id && $target->order >= $this->order) {
            $this->order = $target->order;
        } else {
            $this->order = $target->order + 1;
        }        
        $this->parent_id = $target->parent_id;

        return $this->save();
    }    

    public function getBreadcrumbs()
    {
        $list = [];
        $parent = $this;
        do {
            $list[] = $parent;
        } while ($parent = $parent->getParent());

        return collect(array_reverse($list));
    }

    protected function _branch($id, &$b, $full = false, $l = 0)
    {
        if ($id) {
            if (null === $b) {
                $b = [];
            }

            $f = static::$listTree[$id];
            if (true === $full) {
                $b = array_merge($b, $f->toArray());
            } else {
                $b = array_merge($b, [
                    'id' => $f['id'],
                    'path' => $f['path'],
                    'order' => (int)$f['order'],
                ]);
            }
            $this->listTreeSelect[] = ['value' => $f['id'], 'label' => str_repeat('&nbsp;', $l * 3) . $f['name']];
        }

        if (isset(static::$parentTree[$id])) {
            foreach (static::$parentTree[$id] as $k => $child) {
                $this->_branch($child, $b[self::$childrenKey][$k], $full, $l + 1);
            }
        }
    }

    public function createTree($full = false)
    {
        $this->tree = [];
        static::$listTree = [];
        static::$parentTree = [];

        $list = self::newQuery()->orderBy('parent_id','asc')
            ->orderBy('order','asc')
            ->get();

        foreach ($list as $item) {
            static::$listTree[$item->id] = $item;
            static::$parentTree[$item->parent_id][] = $item->id;
        }

        if (!empty(static::$parentTree[0]) && is_array(static::$parentTree[0])) {
            foreach (static::$parentTree[0] as $k => $item) {
                $this->_branch($item, $this->tree[$k], $full);
            }
        }        

        return $this->tree;
    }

    public function getSelectTree()
    {
        $this->listTreeSelect = [];
        $this->getTree();

        return $this->listTreeSelect;
    }

    public function getTree($full = false)
    {
        $tree = $this->createTree($full);
        if (null === $this->id) {
            return $tree;
        }

        return $this->_findChildrenTree($this->id, $tree);
    }

    protected function _findChildrenTree($id, $tree = array())
    {
        foreach ($tree as $k => $v) {
            if (!empty($v[self::$childrenKey])) {
                if ($v['id'] == $id) {
                    return $v[self::$childrenKey];
                } else {
                    $branch = $this->_findChildrenTree($id, $v[self::$childrenKey]);
                    if ($branch) {
                        return $branch;
                    }
                }
            }
        }

        return [];
    }
}
