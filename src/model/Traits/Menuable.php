<?php namespace Model\Traits;

use Model\Menu;

trait Menuable
{
    /**
     * Get all of the
     */
    public function menu()
    {
        return $this->morphMany(\Model\Menu::class, 'menuable');
    }

    public function addMenu($id)
    {
        $newMenu = $this->menu()->create([
            'name' => $this->name,
            'parent_id' => $id
        ]);

        $newMenu->path = $this->path;
        $newMenu->save();        
    }

    public function syncMenu($ids)
    {
        $nowMenu = $this->menu;
        $nowMenuList = [];
        $nowMenuListKey = [];
        if (null !== $nowMenu) {
            $nowMenuList = $nowMenu->pluck('id', 'parent_id')->toArray();
            $nowMenuListKey = array_keys($nowMenuList);
        }

        $attachMenus = array_diff($ids, $nowMenuListKey);
        $detachMenus = array_diff($nowMenuListKey, $ids);

        foreach ($attachMenus as $menuId) {
            $this->addMenu($menuId);
        }

        foreach ($detachMenus as $menuId) {
            Menu::find($nowMenuList[$menuId])->delete();
        }
    }

    protected static function bootMenuable()
    {
        static::deleted(function($model) {
            $menu = $model->menu;
            if (null === $menu) {
                return ;
            }
            foreach ($menu as $item) {
                $item->delete();
            }
        });
    }
}
