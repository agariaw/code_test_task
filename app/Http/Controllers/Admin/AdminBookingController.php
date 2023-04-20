<?php

namespace DTApi\Http\Controllers;

use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    public function __construct(
        protected BookingRepository $bookingRepo
    ) {
        $this->middleware('is_admin');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return $this->bookingRepo->getAll($request);
    }
}
