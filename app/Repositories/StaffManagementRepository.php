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
            if (is_numeric($id)) {
                return Staff::findOrFail($id);
            } else {
                return Staff::where('staff_id', $id)->firstOrFail();
            }
        }catch (\Exception $e){
            return null;
        }
    }

    public function getLastStaffIdData()
    {
        try{
            return Staff::orderByRaw("CAST(SUBSTRING(staff_id, 7) AS UNSIGNED) DESC")->first();
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

    public function updateStaffPassword($newPass, $staffId)
    {
        try{
            return \App\Models\User::where('username', $staffId)->update(['password' => $newPass]);
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
