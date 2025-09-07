<?php

namespace App\Repositories;

use App\Models\TimeTable;

class TimeTableManagementRepository
{
    public function __construct()
    {
//
    }

    public function getTimeTableListData($search, $perPage, $sortedBy, $sortDirection)
    {
        try{
            return TimeTable::search($search)->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function saveTimeTableData(TimeTable $timeTable): bool|string
    {
        try{
            return $timeTable->save();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function getTimeTableByIdData($id)
    {
        try{
            return TimeTable::where('id', $id)->first();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function deleteTimeTableData($timetable_id)
    {
        try{
            $timetable = TimeTable::where('id', $timetable_id)->first();
            if(!$timetable){
                return false;
            }
            return $timetable->delete();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function saveWeekTimeTableData(array $timetable): true|string
    {
        try {
            TimeTable::insert($timetable);
            return true;
        } catch (\Exception $e) {
            return 'Error : '.$e->getMessage();
        }
    }
}
