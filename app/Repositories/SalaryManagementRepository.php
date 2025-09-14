<?php

namespace App\Repositories;

use App\Models\Salary;
use App\Models\SalaryDeduction;
use App\Models\SalaryStructure;
use App\Models\Staff;
use App\Models\Teacher;

class SalaryManagementRepository
{

    public function getSalaryStructureList(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection)
    {
        try{
            return SalaryStructure::search($search)->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function saveSalaryStructureData(SalaryStructure $salary): bool|string
    {
        try{
            return $salary->save();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getSalaryStructureByIdData(int $id)
    {
        try{
            return SalaryStructure::findOrFail($id);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getSalaryDeductionListData(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection)
    {
        try{
            return SalaryDeduction::search($search)->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function saveSalaryDeductionData(SalaryDeduction $salary): bool|string
    {
        try{
            return $salary->save();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getSalaryDeductionByIdData(int $id)
    {
        try{
            return SalaryDeduction::findOrFail($id);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getMonthlySalaryListData(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection, mixed $month, mixed $year)
    {
        try{
            $query = Salary::search($search)->with(['teacher', 'staff']);

            if (!empty($month) && !empty($year)) {
                $query->whereMonth('payment_date', $month)
                    ->whereYear('payment_date', $year);
            } elseif (!empty($month)) {
                $query->whereMonth('payment_date', $month);
            } elseif (!empty($year)) {
                $query->whereYear('payment_date', $year);
            }
            return $query->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getTeachersListData(): \Illuminate\Database\Eloquent\Collection|string
    {
        try {
            return Teacher::all();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getStaffListData(): \Illuminate\Database\Eloquent\Collection|string
    {
        try{
            return Staff::all();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getSalaryStructuresData(): \Illuminate\Database\Eloquent\Collection|string
    {
        try{
            return SalaryStructure::all();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getSalaryDeductionsData(): \Illuminate\Database\Eloquent\Collection|string
    {
        try{
            return SalaryDeduction::all();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function saveSalaryData(Salary $salary): bool|string
    {
        try{
            return $salary->save();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

}
