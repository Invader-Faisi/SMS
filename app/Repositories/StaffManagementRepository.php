<?php

namespace App\Repositories;

use App\Models\Staff;
use App\Models\User;

class StaffManagementRepository
{
    public function __construct()
    {
//
    }

    public function getStaffListData($search, $perPage, $sortedBy, $sortDirection)
    {
        try{
            return Staff::search($search)->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function getStaffByIdData($id)
    {
        try{
            return Staff::findOrfail($id);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function getLastStaffIdData()
    {
        try{
            return Staff::Latest('id')->first();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function saveStaffData(Staff $staff): bool|string
    {
        try{
            return $staff->save();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function updateStaffData($staffData, $staffId)
    {
        try{
            return Staff::where('staff_id', $staffId)->update($staffData);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function deleteStaffData($staffId)
    {
        try{
            return Staff::where('staff_id', $staffId)->delete();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function addToUsersData(User $newUser): bool|string
    {
        try{
            return $newUser->save();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }
}
