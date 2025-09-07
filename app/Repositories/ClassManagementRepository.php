<?php

namespace App\Repositories;


use App\Models\Classes;
use App\Models\Teacher;

class ClassManagementRepository
{
    public function __construct()
    {
//
    }

    public function getClassesListData($search): \Illuminate\Database\Eloquent\Collection|string
    {
        try{
            return Classes::search($search)->get();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getTeachersListData(): \Illuminate\Database\Eloquent\Collection|string
    {
        try{
            return Teacher::all();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function updateClassData(?string $class_id, ?string $teacher_id, ?string $academic_year)
    {
        try{
            return Classes::where('class_id', $class_id)->update(['teacher_id' => $teacher_id , 'academic_year'=> $academic_year]);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getClassByIdData($id)
    {
        try{
            if (is_numeric($id)) {
                return Classes::findOrFail($id);
            } else {
                return Classes::where('class_id', $id)->firstOrFail();
            }
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }

    }


}
