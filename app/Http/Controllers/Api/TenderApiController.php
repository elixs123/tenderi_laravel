<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TenderWorkflow;
use Illuminate\Http\JsonResponse;

class TenderApiController extends Controller
{
    public function acceptedTenders(): JsonResponse
    {
        $acceptedStatuses = ['accepted', 'documentation_uploaded', 'offer_submitted', 'completed', 'won', 'lost'];

        $users = User::whereHas('workflows', fn($q) => $q->whereIn('status', $acceptedStatuses))
            ->with([
                'workflows' => function ($q) use ($acceptedStatuses) {
                    $q->whereIn('status', $acceptedStatuses)
                      ->with([
                          'procedure:id,number,notice_number,name,type,cpvcodeid,contracting_authority_id,contracting_authority_name,contracting_authority_city_name,contracting_authority_tax_number,contracting_authority_administrative_unit_name,announced',
                          'procedure.lots:id,procedure_id,no,name,short_description,estimated_value,application_deadline_date_time',
                          'tasks' => fn($tq) => $tq->whereNotNull('file_path'),
                      ]);
                }
            ])
            ->get();

        $result = $users->map(function (User $user) {
            return [
                'id'        => $user->id,
                'firstName' => $user->first_name,
                'lastName'  => $user->last_name,
                'email'     => $user->email,
                'tenders'   => $user->workflows->map(function (TenderWorkflow $workflow) {
                    $procedure = $workflow->procedure;

                    $documents = $workflow->tasks->map(fn($task) => [
                        'id'       => $task->id,
                        'naziv'    => $task->naziv,
                        'fileName' => $task->file_name,
                        'fileUrl'  => $task->file_path ? asset('storage/' . $task->file_path) : null,
                        'status'   => $task->status,
                    ]);

                    if ($workflow->document_path) {
                        $documents->prepend([
                            'id'       => null,
                            'naziv'    => 'Tenderska dokumentacija',
                            'fileName' => basename($workflow->document_path),
                            'fileUrl'  => asset('storage/' . $workflow->document_path),
                            'status'   => 'uploaded',
                        ]);
                    }

                    return [
                        'workflowId'                               => $workflow->id,
                        'workflowStatus'                           => $workflow->status,
                        'externalId'                               => $procedure?->id,
                        'number'                                   => $procedure?->number,
                        'noticeNumber'                             => $procedure?->notice_number,
                        'name'                                     => $procedure?->name,
                        'type'                                     => $procedure?->type,
                        'cpvCode'                                  => $procedure?->cpvcodeid,
                        'ContractingAuthorityName'                 => $procedure?->contracting_authority_name,
                        'ContractingAuthorityCityName'             => $procedure?->contracting_authority_city_name,
                        'ContractingAuthorityTaxNumber'            => $procedure?->contracting_authority_tax_number,
                        'ContractingAuthorityAdministrativeUnitName' => $procedure?->contracting_authority_administrative_unit_name,
                        'Announced'                                => $procedure?->announced?->toIso8601String(),
                        'acceptedLots'                             => $workflow->accepted_lots,
                        'lots'                                     => $procedure?->lots->map(fn($lot) => [
                            'id'              => $lot->id,
                            'no'              => $lot->no,
                            'name'            => $lot->name,
                            'shortDescription' => $lot->short_description,
                            'estimatedValue'  => $lot->estimated_value,
                            'deadline'        => $lot->application_deadline_date_time,
                        ]),
                        'documents'                                => $documents->values(),
                        'winnerSupplier'                           => $workflow->winner_supplier,
                        'finalPrice'                               => $workflow->final_price,
                    ];
                }),
            ];
        });

        return response()->json($result);
    }
}
