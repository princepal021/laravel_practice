<?php

namespace App\Services;

use App\Models\{
    User,
    Role,
    Permission,
    Institutes
};
use App\Http\Resources\PaginationResource;
use App\Services\RoleService;
use App\Services\CommunicationService;
use App\Services\InstituteUserService;
use App\Services\InstituteService;

class UserService extends PaginationResource
{
    protected $service;
    protected $roleService;
    protected $communicationService;
    protected $instituteUserService;
    protected $instituteService;
    public function __construct(User $user, RoleService $roleService, CommunicationService $communicationService, InstituteUserService $instituteUserService, Institutes $instituteService) {
        $this->service = $user;
        $this->roleService = $roleService;
        $this->communicationService = $communicationService;
        $this->instituteUserService = $instituteUserService;
        $this->instituteService = $instituteService;
    }
    public function paginate($request) {
        $response = $this->service->with('role')->where(function($query) use($request) {
                $query->where('deleted', false);
                if($request->status) {
                    $query->where('status', $request->status == 'true'?true:false);
                }
                if($request->userType == User::INSTITUTE) {
                    $query->where('userType', User::INSTITUTE);
                } else {
                    $query->where('userType', '!=', User::INSTITUTE);
                }             
                if (getCurrentUserByKey('userType') == User::INSTITUTE) {
                    $query->whereIn('_id', getUserIds());
                }
            })->select('_id', 'firstName', 'lastName', 'email', 'status', 'roleId')->paginate(pagination($request->per_page));
        return parent::jsonResponse($response);
    }
    public function helperList() {
        $status = commonStatus();
        $userTypes = userTypes();
        if (getCurrentUserByKey('userType') == User::INSTITUTE) {
            $userTypes = [];
            $roles = $this->roleService->whereRoleFor(User::INSTITUTE);
            $institutes = $this->instituteService->select('_id', 'instituteTitle')->where('_id', getInstituteId())->where('deleted', false)->get();
        } else {
            $roles = $this->roleService->all();   
            $institutes = $this->instituteService->select('_id', 'instituteTitle')->where('deleted', false)->get();            
        }
        $loginByTypes = loginByTypes();
        $loginOtpSendTypes = loginOtpSendTypes();
        return compact('roles','status','userTypes','loginByTypes','loginOtpSendTypes','institutes');
    }
    public function store($request)
    {
        $email = $request->input('email');
        $randomPassword = sha1(mt_rand(10000,99999).time().$email);
        $password = $request->input('password')??$randomPassword;
        $createdBy = createdByObject();

        $service = $this->service;
        $service->firstName = $request->input('firstName');
        $service->lastName = $request->input('lastName');
        $service->email = $email;
        $service->phone = $request->input('phone');
        $service->userType = $request->input('userType')??User::INSTITUTE;
        $service->loginBy = $request->input('loginBy');
        $service->loginOtpScannedBy = $request->input('loginOtpScannedBy');
        $service->roleId = $request->input('roleId');
        $service->status = $request->input('status');
        $service->email_verified_at = dateTime(); 
        $service->password = bcrypt($password);
        $service->createdBy = $createdBy;
        $service->updatedBy = $createdBy;
        $service->deleted = false;
        $service->created_at = dateTime();
        $service->updated_at = dateTime();
        $service->save();

        if ($request->instituteId) {
            $this->instituteUserService->store($request->instituteId, $service->_id);
        }

        $name = $request->input('name');
        $emailSubject = 'Login details at '.appName();
        $emailBody = view('Email.RegisterVerifyEmailLink', compact('name', 'password', 'email'));
        $this->communicationService->mail($email, $emailSubject, $emailBody);
        return $service;
    }
    public function get($id = null) {
        return $this->service->find($id);
    }
    public function update($request, $service) {
        $password = $request->input('password');
        $createdBy = createdByObject();
        if(!is_object($service)) {
            $service = $this->get($service);
        }
        if ($request->has('firstName')) {
            $service->firstName = $request->input('firstName');
        }
        if ($request->has('lastName')) {
            $service->lastName = $request->input('lastName');
        }
        if ($request->has('email')) {
            $service->email = $request->input('email');
        }
        if ($request->has('phone')) {
            $service->phone = $request->input('phone');
        }
        if ($request->has('phoneCode')) {
            $service->phoneCode = $request->input('phoneCode');
        }
        if ($request->has('photo')) {
            $service->photo = $request->input('photo');
        }
        if ($request->has('userType')) {
            $service->userType = $request->input('userType')??User::INSTITUTE;
        }
        if ($request->has('loginBy')) {
            $service->loginBy = $request->input('loginBy');
        }
        if ($request->has('loginOtpScannedBy')) {
            $service->loginOtpScannedBy = $request->input('loginOtpScannedBy');
        }
        if ($request->has('roleId')) {
            $service->roleId = $request->input('roleId');
        }
        if ($request->has('status')) {
            $service->status = $request->input('status');
        }
        if ($request->has('password') && $password) {
            $service->password = bcrypt($password);
        }
        $service->updatedBy = $createdBy;
        $service->updated_at = dateTime();
        $service->save();

        if ($request->instituteId) {
            $this->instituteUserService->store($request->instituteId, $service->_id);
        }

        return $service;
    }
    public function delete($service = null)
    {
        if(!is_object($service)) {
            $service = $this->get($service);
        }
        $service->deleted = true;
        $service->save();
        return $service;
    }
    public function updateStatus($request) {
        $this->get($request->userId)->update(['status' => $request->status]);
    }
    public function getInstituteTypeUsers() {
        return $this->service->where('userType', User::INSTITUTE)->where('deleted', false)->get();
    }
    public function getInstituteUsers() {
        return $this->service->where('userType', User::INSTITUTE)->where('deleted', false)->whereIn('_id', getUserIds())->get();
    }
}