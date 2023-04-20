<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use Exception;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    public function __construct(
        protected BookingRepository $bookingRepo
    ) {
        $this->middleware('auth');
    }

    /**
     * @param AllBookingRequest $request
     * @return mixed
     */
    public function index(
        AllBookingRequest $request
    ): JsonResponse {
        try {
            return response($this->bookingRepo->getUsersJobs($user_id));
        } catch (Exception $e) {
            //TODO:: Log exception
            return error('Unknown error');
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show(
        int|string $id
    ): JsonResponse {  
        try {
            return response($this->bookingRepo->with('translatorJobRel.user')->find($id));
        } catch (Exception $e) {
            //TODO:: Log exception
            return error('Unknown error');
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->all();

        $response = $this->bookingRepo->store(
            $request->__authenticatedUser,
            $request->get('from_language_id'),
            $request->get('immediate'),
            $request->get('due_time'),
            $request->get('customer_phone_type'),
            $request->get('duration'),
            ...
        );

        return response($response);

    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update(string|int $id, Request $request): JsonResponse
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->bookingRepo->updateJob(
            $id, 
            $request->get('from_language_id'),
            $request->get('immediate'),
            $request->get('due_time'),
            $request->get('customer_phone_type'),
            $request->get('duration'),
            ...,
            $cuser
        );

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request): JsonResponse
    {
        $data = $request->all();

        $response = $this->bookingRepo->storeJobEmail($data);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(AllBookingRequest $request): JsonResponse
    {
        $response = $this->bookingRepo->getUsersJobsHistory(
            $request->get('user_id'), 
            $request
        );
        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request): JsonResponse
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepo->acceptJob($data, $user);

        return response($response);
    }

    public function acceptJobWithId(Request $request): JsonResponse
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepo->acceptJobWithId($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request): JsonResponse
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepo->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request): JsonResponse
    {
        $data = $request->all();

        $response = $this->bookingRepo->endJob($data);

        return response($response);

    }

    public function customerNotCall(Request $request): JsonResponse
    {
        $data = $request->all();

        $response = $this->bookingRepo->customerNotCall($data);

        return response($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request): JsonResponse
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;

        $response = $this->bookingRepo->getPotentialJobs($user);

        return response($response);
    }

    public function distanceFeed(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data = $request->all();

            if (isset($data['distance']) && $data['distance'] != "") {
                $distance = $data['distance'];
            } else {
                $distance = "";
            }
            if (isset($data['time']) && $data['time'] != "") {
                $time = $data['time'];
            } else {
                $time = "";
            }
            if (isset($data['jobid']) && $data['jobid'] != "") {
                $jobid = $data['jobid'];
            }

            if (isset($data['session_time']) && $data['session_time'] != "") {
                $session = $data['session_time'];
            } else {
                $session = "";
            }

            if ($data['flagged'] == 'true') {
                if($data['admincomment'] == '') return "Please, add comment";
                $flagged = 'yes';
            } else {
                $flagged = 'no';
            }
            
            if ($data['manually_handled'] == 'true') {
                $manually_handled = 'yes';
            } else {
                $manually_handled = 'no';
            }

            if ($data['by_admin'] == 'true') {
                $by_admin = 'yes';
            } else {
                $by_admin = 'no';
            }

            if (isset($data['admincomment']) && $data['admincomment'] != "") {
                $admincomment = $data['admincomment'];
            } else {
                $admincomment = "";
            }
            if ($time || $distance) {

                $affectedRows = Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
            }

            if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {

                $affectedRows1 = Job::where('id', '=', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));

            }
            DB::commit();
            return response('Record updated!');
        } catch (\Exception $e) {
            DB::rollback();
            return error("error updating record");
        }

        
    }

    public function reopen(Request $request): JsonResponse
    {
        $data = $request->all();
        $response = $this->bookingRepo->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request): JsonResponse
    {
        $data = $request->all();
        $job = $this->bookingRepo->find($data['jobid']);
        $job_data = $this->bookingRepo->jobToData($job);
        $this->bookingRepo->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request): JsonResponse
    {
        $data = $request->all();
        $job = $this->bookingRepo->find($data['jobid']);
        $job_data = $this->bookingRepo->jobToData($job);

        try {
            $this->bookingRepo->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
