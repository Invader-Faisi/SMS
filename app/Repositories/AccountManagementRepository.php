<?php

namespace App\Repositories;

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

    public function getMonthlyFeeListData(mixed $search, mixed $perPage, mixed $sortedBy, mixed $sortDirection,$month)
    {
        try{
            $query = Fee::search($search)->with('student');
            if($month){
                $query->whereMonth('due_date', $month);
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


}
