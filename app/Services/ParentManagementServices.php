<?php
namespace App\Services;

use App\Models\Parents;
use App\Models\User;
use App\Repositories\ParentManagementRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class ParentManagementServices
{
    protected $parentRepository;

    public function __construct(ParentManagementRepository $parentRepository)
    {
        $this->parentRepository = $parentRepository;
    }

    public function getParentList($search,$perPage,$sortedBy,$sortDirection)
    {
        return $this->parentRepository->getParentListData($search,$perPage,$sortedBy,$sortDirection);
    }

    public function getParentById($id)
    {
        return $this->parentRepository->getParentByIdData($id);
    }

    public function saveParent($parentData): bool|string
    {
        if(!empty($parentData['image']))
        {
            $parentImage = $parentData['image'];
            $imagePath = $parentImage->store('users/parents', 'public');
            $parentData['image'] = $imagePath;
        }

        $lastParent = $this->parentRepository->getLastParentIdData();

        // Generating new parent_id
        if ($lastParent && $lastParent->parent_id) {
            $lastNumber = (int) str_replace('SMS-P-', '', $lastParent->parent_id);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 0001;
        }

        $parentData['parent_id'] = 'SMS-P-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        $parent = new Parents();
        $parent->fill($parentData);

        $response = $this->parentRepository->saveParentData($parent);

        if($response === true){
            $parent['username'] = $parentData['parent_id'];
            return $this->addToUsers($parent);
        }else{
            return $response;
        }
    }

    public function updateParent($parentData,$parentId)
    {
        if(!empty($parentData['image'])){
            if ($parentData['image'] instanceof UploadedFile) {
                // new file uploaded
                $parentImage = $parentData['image'];
                $imagePath = $parentImage->store('users/parents', 'public');
                $parentData['image'] = $imagePath;
            }
        }

        return $this->parentRepository->updateParentData($parentData,$parentId);
    }

    public function deleteParent($parentId)
    {
        return $this->parentRepository->deleteParentData($parentId);
    }

    public function addToUsers($parent): bool|string
    {
        $newUser = new User();
        $newUser['username'] = $parent['username'];
        $newUser['name'] = $parent['name'];
        $newUser['email'] = $parent['email'];
        $newUser['password'] = Hash::make($parent['password']);
        $newUser['mobile'] = $parent['mobile'];
        $newUser['address'] = $parent['address'];

        return $this->parentRepository->addToUsersData($newUser);
    }

}
