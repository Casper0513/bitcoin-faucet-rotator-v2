<?php

namespace App\Http\Controllers\API;

use App\Helpers\Functions\Faucets;
use App\Http\Requests\API\CreateFaucetAPIRequest;
use App\Http\Requests\API\UpdateFaucetAPIRequest;
use App\Models\Faucet;
use App\Models\PaymentProcessor;
use App\Repositories\FaucetRepository;
use App\Transformers\FaucetsTransformer;
use Helpers\Functions\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use Illuminate\Support\Facades\Config;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class FaucetController
 *
 * @author  Rob Attfield <emailme@robertattfield.com> <http://www.robertattfield.com>
 * @package App\Http\Controllers\API
 */

class FaucetAPIController extends AppBaseController
{
    /**
 * @var  FaucetRepository
*/
    private $faucetRepository;
    private $faucetCollection;

    public function __construct(FaucetRepository $faucetRepo)
    {
        $this->faucetRepository = $faucetRepo;
        $this->faucetCollection = $this->faucetRepository->findWhere(
            ['is_paused' => false, 'has_low_balance' => false]
        )->sortBy('interval_minutes')->values();
    }

    public function index(Request $request)
    {
        $this->faucetRepository->pushCriteria(new RequestCriteria($request));
        $this->faucetRepository->pushCriteria(new LimitOffsetCriteria($request));

        for ($i = 0; $i < count($this->faucetCollection); $i++) {
            $this->faucetCollection[$i] = (new FaucetsTransformer)
                ->transform($this->faucetCollection[$i], true);
        }

        return $this->sendResponse($this->faucetCollection, 'Faucets retrieved successfully');
    }

    public function show($slug)
    {

        $faucet = $this->faucetRepository->findWhere(
            ['is_paused' => false, 'has_low_balance' => false, 'slug' => $slug, 'deleted_at' => null]
        )->first();

        if (empty($faucet)) {
            return $this->sendError('Faucet not found',404);
        }

        return $this->sendResponse(
            (new FaucetsTransformer)->transform(
                $faucet,
                    true
            ), 'Faucet retrieved successfully');
    }

    public function getFirstFaucet(){

        return $this->sendResponse(
            (new FaucetsTransformer)->transform(
                $this->faucetCollection[0],
                    true
            ), 'Faucet retrieved successfully');
    }

    public function getPreviousFaucet($slug){
        $faucetSlugs = array_column($this->faucetCollection->toArray(), 'slug');
        $previousFaucet = null;

        foreach($faucetSlugs as $key => $value){
            if($value == $slug){
                // Decrement key to find previous one.
                if($key - 1 < 0){
                    // If subtracted value is negative,
                    // we are at beginning of faucet collection array.
                    // Go to last faucet in the collection.
                    $previousFaucet = $this->faucetRepository->findWhere(
                        [
                            'is_paused' => false,
                            'has_low_balance' => false,
                            'slug' => $faucetSlugs[count($faucetSlugs) - 1],
                            'deleted_at' => null
                        ]
                    )->first();

                    return $this->sendResponse(
                        (new FaucetsTransformer)->transform(
                            $previousFaucet,
                                true
                        ), 'Faucet retrieved successfully');
                }

                $previousFaucet = $this->faucetRepository->findWhere(
                    [
                        'is_paused' => false,
                        'has_low_balance' => false,
                        'slug' => $faucetSlugs[$key - 1],
                        'deleted_at' => null
                    ]
                )->first();

                return $this->sendResponse(
                    (new FaucetsTransformer)->transform(
                        $previousFaucet,
                        true
                    ), 'Faucet retrieved successfully');
            }
        }
        return null;
    }

    public function getNextFaucet($slug){
        $faucetSlugs = array_column($this->faucetCollection->toArray(), 'slug');
        $nextFaucet = null;

        foreach($faucetSlugs as $key => $value){
            if($value == $slug){
                // Increase key to find next one.
                if($key + 1 > count($faucetSlugs) - 1){
                    // If addition is greater than number of faucets,
                    // We are at end of the collection.
                    // Go to first faucet in the collection.
                    $nextFaucet = $this->faucetRepository->findWhere(
                        [
                            'is_paused' => false,
                            'has_low_balance' => false,
                            'slug' => $faucetSlugs[0],
                            'deleted_at' => null
                        ]
                    )->first();

                    return $this->sendResponse(
                        (new FaucetsTransformer)->transform(
                            $nextFaucet,
                            true
                        ), 'Faucet retrieved successfully');
                }
                $nextFaucet = $this->faucetRepository->findWhere(
                    [
                        'is_paused' => false,
                        'has_low_balance' => false,
                        'slug' => $faucetSlugs[$key + 1],
                        'deleted_at' => null
                    ]
                )->first();

                return $this->sendResponse(
                    (new FaucetsTransformer)->transform(
                        $nextFaucet,
                        true
                    ), 'Faucet retrieved successfully');
            }
        }
        return null;
    }

    public function getLastFaucet(){

        return $this->sendResponse(
            (new FaucetsTransformer)->transform(
                $this->faucetCollection[count($this->faucetCollection) - 1],
                true
            ), 'Faucet retrieved successfully');
    }

    public function paymentProcessorFaucet($paymentProcessorSlug, $faucetSlug)
    {
        //Obtain payment processor by related slug.
        $paymentProcessor = PaymentProcessor::where('slug', '=', $paymentProcessorSlug)->firstOrFail();

        // Use model relationship to obtain associated faucets
        $faucet = $paymentProcessor->faucets()
            ->where('is_paused', '=', false)
            ->where('has_low_balance', '=', false)
            ->where('deleted_at', '=', null)
            ->where('slug', '=', $faucetSlug)
            ->first();
        if ($faucet == null || !$faucet) {
            return [404 => 'Not Found'];
        }

        return $this->sendResponse(
            (new FaucetsTransformer)->transform(
                $faucet,
                true
            ), 'Faucet retrieved successfully');
    }

    public function paymentProcessorFaucets($paymentProcessorSlug)
    {
        //Obtain payment processor by related slug.
        $paymentProcessor = PaymentProcessor::where('slug', '=', $paymentProcessorSlug)->firstOrFail();

        // Use model relationship to obtain associated faucets
        $faucets = $paymentProcessor->faucets()
            ->where('is_paused', '=', false)
            ->where('has_low_balance', '=', false)
            ->where('deleted_at', '=', null)
            ->orderBy('interval_minutes')
            ->get();

        for ($i = 0; $i < count($faucets); $i++) {
            $faucets[$i] = (new FaucetsTransformer)->transform($faucets[$i], true);
        }

        return $this->sendResponse($faucets, 'Faucets retrieved successfully');
    }

    public function firstPaymentProcessorFaucet($paymentProcessorSlug)
    {
        //Obtain payment processor by related slug.
        $paymentProcessor = PaymentProcessor::where('slug', '=', $paymentProcessorSlug)->firstOrFail();

        // Use model relationship to obtain associated faucets
        $faucets = $paymentProcessor->faucets()
            ->where('is_paused', '=', false)
            ->where('has_low_balance', '=', false)
            ->where('deleted_at', '=', null)
            ->orderBy('interval_minutes')
            ->get();

        $faucet = (new FaucetsTransformer)->transform($faucets[0], true);

        return $this->sendResponse($faucet, 'Faucet retrieved successfully');

    }

    public function previousPaymentProcessorFaucet($paymentProcessorSlug, $faucetSlug){
        //Obtain payment processor by related slug.
        $paymentProcessor = PaymentProcessor::where('slug', '=', $paymentProcessorSlug)->firstOrFail();

        // Use model relationship to obtain associated faucets
        $faucets = $paymentProcessor->faucets()
            ->orderBy('interval_minutes');

        $array = array_column($faucets->get()->toArray(), 'slug');

        foreach($array as $key => $value){
            if($value == $faucetSlug){
                // Increase key to find next one.
                if($key - 1 > count($array) - 1){
                    // If addition is greater than number of faucets,
                    // We are at end of the collection.
                    // Go to first faucet in the collection.
                    $faucet = Faucet::where('is_paused', '=', false)
                        ->where('has_low_balance', '=', false)
                        ->where('deleted_at', '=', null)
                        ->where('slug', '=', $array[0])
                        ->orderBy('interval_minutes')
                        ->first();

                    return $this->sendResponse(
                        (new FaucetsTransformer)->transform(
                            $faucet,
                            true
                        ), 'Faucet retrieved successfully');
                }

                $faucet = Faucet::where('is_paused', '=', false)
                    ->where('has_low_balance', '=', false)
                    ->where('deleted_at', '=', null)
                    ->where('slug', '=', $array[$key - 1])
                    ->orderBy('interval_minutes')
                    ->first();

                return $this->sendResponse(
                    (new FaucetsTransformer)->transform(
                        $faucet,
                        true
                    ), 'Faucet retrieved successfully');
            }
        }
        return null;
    }

    public function nextPaymentProcessorFaucet($paymentProcessorSlug, $faucetSlug){
        //Obtain payment processor by related slug.
        $paymentProcessor = PaymentProcessor::where('slug', '=', $paymentProcessorSlug)->firstOrFail();

        // Use model relationship to obtain associated faucets
        $faucets = $paymentProcessor->faucets()
            ->orderBy('interval_minutes');

        $array = array_column($faucets->get()->toArray(), 'slug');

        foreach($array as $key => $value){
            if($value == $faucetSlug){
                // Increase key to find next one.
                if($key + 1 > count($array) - 1){
                    // If addition is greater than number of faucets,
                    // We are at end of the collection.
                    // Go to first faucet in the collection.
                    $faucet = Faucet::where('is_paused', '=', false)
                        ->where('has_low_balance', '=', false)
                        ->where('deleted_at', '=', null)
                        ->where('slug', '=', $array[0])
                        ->orderBy('interval_minutes')
                        ->first();

                    return $this->sendResponse(
                        (new FaucetsTransformer)->transform(
                            $faucet,
                            true
                        ), 'Faucet retrieved successfully');
                }

                $faucet = Faucet::where('is_paused', '=', false)
                    ->where('has_low_balance', '=', false)
                    ->where('deleted_at', '=', null)
                    ->where('slug', '=', $array[$key + 1])
                    ->orderBy('interval_minutes')
                    ->first();

                return $this->sendResponse(
                    (new FaucetsTransformer)->transform(
                        $faucet,
                        true
                    ), 'Faucet retrieved successfully');
            }
        }
        return null;
    }

    public function lastPaymentProcessorFaucet($paymentProcessorSlug){
        //Obtain payment processor by related slug.
        $paymentProcessor = PaymentProcessor::where('slug', '=', $paymentProcessorSlug)->firstOrFail();

        // Use model relationship to obtain associated faucets
        $faucets = $paymentProcessor->faucets()
            ->where('is_paused', '=', false)
            ->where('has_low_balance', '=', false)
            ->where('deleted_at', '=', null)
            ->orderBy('interval_minutes')
            ->get();

        $faucet = (new FaucetsTransformer)->transform($faucets[count($faucets) - 1], true);

        return $this->sendResponse($faucet, 'Faucet retrieved successfully');
    }

}
