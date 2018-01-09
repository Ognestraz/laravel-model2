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

    public function addToMenu($id)
    {
        $menu = Menu::find($id);
//        $newMenu = new Menu();
//        $newMenu->name = $this->name;
//        $newMenu->parent_id = $menu->id;
//        $newMenu->save();
//        
//        $newMenu->path = $this->path;
//        $newMenu->save();
        
        $newMenu = $this->menu()->create([
            'name' => $this->name,
           // 'path' => $this->path,
            'parent_id' => $menu->id
        ]);
        //print_r($newMenu->getAttributes());
        $newMenu->path = $this->path;
        $newMenu->save();        
        
    }
}
