<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\User;
use App\Repositories\StaffManagementRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class StaffManagementServices
{
    protected $staffRepository;

    public function __construct(StaffManagementRepository $staffRepository)
    {
        $this->staffRepository = $staffRepository;
    }

    public function getStaffList($search,$perPage,$sortedBy,$sortDirection)
    {
        return $this->staffRepository->getStaffListData($search,$perPage,$sortedBy,$sortDirection);
    }

    public function getStaffById($id)
    {
        return $this->staffRepository->getStaffByIdData($id);
    }

    public function saveStaff($staffData): bool|string
    {
        if(!empty($staffData['image']))
        {
            $staffImage = $staffData['image'];
            $imagePath = $staffImage->store('users/staffs', 'public');
            $staffData['image'] = $imagePath;
        }

        $lastStaff = $this->staffRepository->getLastStaffIdData();

        // Generating new staff_id
        if ($lastStaff && $lastStaff->staff_id) {
            $lastNumber = (int) str_replace('SMS-S-', '', $lastStaff->staff_id);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 0001;
        }

        $staffData['staff_id'] = 'SMS-S-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $staff = new Staff();
        $staff->fill($staffData);

        $response = $this->staffRepository->saveStaffData($staff);

        if($response === true){
            $staff['username'] = $staffData['staff_id'];
            return $this->addToUsers($staff);
        }else{
            return $response;
        }
    }

    public function updateStaff($staffData,$staffId)
    {
        if(!empty($staffData['image'])){
            if ($staffData['image'] instanceof UploadedFile) {
                // new file uploaded
                $staffImage = $staffData['image'];
                $imagePath = $staffImage->store('users/staffs', 'public');
                $staffData['image'] = $imagePath;
            }
        }

        return $this->staffRepository->updateStaffData($staffData,$staffId);
    }

    public function deleteStaff($staffId)
    {
        return $this->staffRepository->deleteStaffData($staffId);
    }

    public function addToUsers($staff): bool|string
    {
        $newUser = new User();
        $newUser['username'] = $staff['username'];
        $newUser['role'] = 'Staff';
        $newUser['password'] = Hash::make($staff['password']);

        return $this->staffRepository->addToUsersData($newUser);
    }

}
