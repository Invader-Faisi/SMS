<?php

namespace App\Services;

use App\Repositories\ClassManagementRepository;
use App\Repositories\TeacherManagementRepository;
use App\Repositories\TimeTableManagementRepository;
use Carbon\Carbon;

class TimeTableManagementServices
{
    protected $timeTableRepository;
    protected $teacherRepository;
    protected $classRepository;

    public function __construct(TimeTableManagementRepository $timeTableRepository, TeacherManagementRepository $teacherRepository, ClassManagementRepository $classRepository)
    {
        $this->timeTableRepository = $timeTableRepository;
        $this->teacherRepository = $teacherRepository;
        $this->classRepository = $classRepository;
    }

    public function getTimeTableList($search,$perPage,$sortedBy,$sortDirection)
    {
        return $this->timeTableRepository->getTimeTableListData($search, $perPage, $sortedBy, $sortDirection);
    }

    public function getTeacherList(): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->teacherRepository->getTeacherListForTimeTable();
    }

    public function getClassesList($search): \Illuminate\Database\Eloquent\Collection|string
    {
        return $this->classRepository->getClassesListData($search);
    }

    public function getTimeTableById($id)
    {
        return $this->timeTableRepository->getTimeTableByIdData($id);
    }

    public function deleteTimeTable($timetable_id)
    {
        return $this->timeTableRepository->deleteTimeTableData($timetable_id);
    }

    public function saveWeekTimeTable(array $bulkData): true|string
    {
        return $this->timeTableRepository->saveWeekTimeTableData($bulkData);
    }

    public function getPeriod($id)
    {
        return $this->timeTableRepository->getPeriodData($id);
    }

    public function updatePeriod($period, $timetable_id)
    {
        if($period['period'] !== 1)
        {
            $previous_period =  $this->timeTableRepository->getPeriodData($timetable_id, $period['period'] - 1, $period['class_id']);
            if($previous_period){
                $minStart = Carbon::parse($previous_period->end_time)->addMinutes(5);
                $currentStart = Carbon::parse($period['start_time']);

                if ($currentStart->lt($minStart)) {
                    return false;
                }

                return $this->timeTableRepository->updatePeriodData($period, $timetable_id);
            }
        }
        return $this->timeTableRepository->updatePeriodData($period, $timetable_id);
    }

    public function getTimeTableByClass(string $class)
    {
        return $this->timeTableRepository->getTimeTableByClassData($class);
    }

}
