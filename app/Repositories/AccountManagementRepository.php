<?php

namespace App\Repositories;

use Carbon\Carbon;
use App\Models\Fee;
use App\Models\FeeStructure;

class AccountManagementRepository
{
    function __construct()
    {
//
    }

    public function getFeeStructureListData(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection)
    {
        try{
            return FeeStructure::search($search)->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function saveFeeStructureData(FeeStructure $feeStructure): bool|string
    {
        try{
            return $feeStructure->save();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }

    }

    public function getFeeStructureByIdData($feeId)
    {
        try{
            return FeeStructure::findOrFail($feeId);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function updateFeeStructureData(array $feeForm, mixed $id)
    {
        try{
            return FeeStructure::where("id", $id)->update($feeForm);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function deleteFeeStructureData($id): int|string
    {
        try{
            return FeeStructure::destroy($id);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getAllFeeStructureData(): \Illuminate\Database\Eloquent\Collection|string
    {
        try{
            return FeeStructure::all();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getOneTimeFeeStructureData($class)
    {
        try{
            return FeeStructure::where('class', $class)
                ->where('frequency', 'One Time')
                ->get();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getAnnualStructureData($class)
    {
        try{
            return FeeStructure::where('class', $class)
                ->where('frequency', 'Yearly')
                ->get();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

//---------------------------------------Fee Structures End Here---------------------------------------------//

    public function getMonthFeeByStudentId($student_id,$struct_id, mixed $month, int $year)
    {
        try{
            return Fee::where('student_id', $student_id)
                ->where('fee_structure_id', $struct_id)
                ->whereMonth('due_date', $month)
                ->whereYear('due_date', $year)
                ->exists();

        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function saveMonthlyFeeData(Fee $fee): bool|string
    {
        try{
            return $fee->save();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getMonthlyFeeListData(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection,$month, $year)
    {
        try{
             $query = Fee::search($search)->with('student');

                if (!empty($month) && !empty($year)) {
                    $query->whereMonth('due_date', $month)
                        ->whereYear('due_date', $year);
                } elseif (!empty($month)) {
                    $query->whereMonth('due_date', $month);
                } elseif (!empty($year)) {
                    $query->whereYear('due_date', $year);
                }

                return $query->orderBy($sortedBy, $sortDirection)->paginate($perPage);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getMonthlyFeeById($feeId)
    {
        try{
            return Fee::findOrFail($feeId);
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function addOneTimeFeeData(Fee $oneTimeFee): bool|string
    {
        try{
            return $oneTimeFee->save();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getMonthlyFeesByStudentAndMonthData($studentId, $month)
    {
         $carbonDate = Carbon::parse($month);
        try{
            return Fee::where('student_id', $studentId)
                ->where(function ($q) {
                    $q->where('status', 'pending')
                    ->orWhere('status', 'partial');
                })
                ->whereMonth('due_date', $carbonDate->month)
                ->whereYear('due_date', $carbonDate->year)
                ->get();
        }catch (\Exception $e){
            return 'Error: ' . $e->getMessage();
        }
    }


}
