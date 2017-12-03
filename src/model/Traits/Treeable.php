<?php namespace Model\Traits;

use DB;
use Illuminate\Support\Facades\Schema;

trait Treeable
{
    protected $tree;
    protected static $listTree;
    protected static $parentTree;

    protected static function bootTreeable()
    {
        static::saving(function($model) {
            if (true === $model->isDirty('parent_id')) {
                $oldParentId = $model->getOriginal('parent_id');
                if (!empty($oldParentId)) {
                    $model->brothers()
                        ->where('order', '>=', $model->order)
                        ->update([
                            'order' => DB::raw('`order` + 1')
                        ]);
                    self::find($oldParentId)->childs()
                        ->where('order', '>', $model->getOriginal('order'))
                        ->update([
                            'order' => DB::raw('`order` - 1')
                        ]);
                } else {
                    $parent = self::find($model->parent_id);
                    $order = 0;
                    if (null !== $parent && 0 !== $parent->childs()->count()) {
                        $order = $parent->childs()->max('order') + 1;
                    }
                    $model->order = $order;
                }
            } elseif (true === $model->isDirty('order')) {
                $oldOrder = $model->getOriginal('order');
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
        });

        self::deleted(function($model) {
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
        return self::where('parent_id', $this->id ?: 0);
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

    protected function _branch($id, &$b, $model = false)
    {
        if ($id) {
            //$b['id'] = $this->listTree[$id]->id;
            if (!$model) {
                $fieldsTree = array_merge($this->mainFieldsTree, $this->addFieldsTree);
                foreach ($fieldsTree as $val) {
                    $b[$val] = static::$listTree[$id]->$val;
                }
            } else {
                $b['model'] = static::$listTree[$id];
            }
        }

        if (isset(static::$parentTree[$id])) {
            foreach (static::$parentTree[$id] as $k => $child) {
                $this->_branch($child, $b['childs'][$k], $model);
            }
        }
    }

    public function createTree($model = false)
    {
        $this->tree = array();

        $list = self::newQuery()->orderBy('parent_id','asc')
            ->orderBy('order','asc')
            ->get();

        foreach ($list as $item) {
            static::$listTree[$item->id] = $item;
            static::$parentTree[$item->parent_id][] = $item->id;
        }

        if (!empty(static::$parentTree[0]) && is_array(static::$parentTree[0])) {
            foreach (static::$parentTree[0] as $k => $item) {
                $this->_branch($item, $this->tree[$k], $model);
            }
        }
        
        return $this->tree;
    }
    
    public function getTree()
    {
        $tree = $this->createTree(true);
        return $this->_findChildrenTree($this->id, $tree);
    }

    protected function _findChildrenTree($id, $tree = array())
    {
        foreach ($tree as $k => $v) {
            if (!empty($v['childs'])) {
                if ($v['model']->id == $id) {
                    return $v['childs'];
                } else {
                    $branch = $this->_findChildrenTree($id, $v['childs']);
                    if ($branch) {
                        return $branch;
                    }                    
                }
            }
        }

        return [];
    }

}
