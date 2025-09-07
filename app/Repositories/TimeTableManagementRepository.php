<?php

namespace App\Repositories;

use App\Models\TimeTable;
use Illuminate\Database\QueryException;

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
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return 'Same Teacher Cannot in two classes at the same time!';
            }
            return 'Error : '.$e->getMessage();
        }
    }

    public function getPeriodData($id, $period = '', $class_id = '')
    {
        try{
            if(empty($period) && empty($class_id)){
                return TimeTable::findOrFail($id);
            }else{
                return TimeTable::where('period', $period)->where('class_id', $class_id)->first();
            }

        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function updatePeriodData($period, $timetable_id)
    {
        try{
            return TimeTable::where('id', $timetable_id)->update($period);
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }

    public function getTimeTableByClassData(string $class)
    {
        try{
            return TimeTable::where('class_id', $class)->get();
        }catch (\Exception $e){
            return 'Error : '.$e->getMessage();
        }
    }
}
