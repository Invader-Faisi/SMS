<?php

namespace App\Repositories;

use App\Models\Parents;

class ParentManagementRepository
{

    public function __construct()
    {
//
    }

    public function getParentListData($search, $perPage, $sortedBy, $sortDirection)
    {
        try{
            return Parents::search($search)->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function getParentByIdData($id)
    {
        try{
            if (is_numeric($id)) {
                return Parents::findOrFail($id);
            } else {
                return Parents::where('parent_id', $id)->firstOrFail();
            }
        }catch (\Exception $e){
            return null;
        }
    }

    public function getLastParentIdData()
    {
        try{
            return Parents::orderByRaw("CAST(SUBSTRING(parent_id, 7) AS UNSIGNED) DESC")->first();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function saveParentData(\App\Models\Parents $parent): bool|string
    {
        try{
            return $parent->save();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function updateParentData($parentData, $parentId)
    {
        try{
            return Parents::where('parent_id', $parentId)->update($parentData);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function updateParentPassword($newPass, $parentId)
    {
        try{
            return \App\Models\User::where('username', $parentId)->update(['password' => $newPass]);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function deleteParentData($parentId)
    {
        try{
            $parent = Parents::where('parent_id', $parentId)->first();

            if (!$parent) {
                return 'Parent not found.';
            }
            $parent->delete();
            return true;
        }catch (\Exception $e){
            if ($e->getCode() == "23000" && str_contains($e->getMessage(), '1451')) {
                return "Cannot delete parent: student records are still linked.";
            }
            return "Error: " . $e->getMessage();
        }
    }

    public function addToUsersData(\App\Models\User $newUser): bool|string
    {
        try{
            return $newUser->save();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }
}
