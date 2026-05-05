<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    protected $fillable = ['id','procedure_id', 'lot_name', 'estimated_value', 'no', 'master_agreement_status', 'status', 'additional_information', 'contract_duration', 'extended_duration_reason', 'has_complaint', 'location', 'master_agreement_duration', 'master_agreement_duration_interval_type', 'quantity', 'short_description', 'phase_number', 'application_deadline_date_time', 'bid_opening_date_time', 'documentation_take_over_deadline_date', 'intermediate_phase_documentation_download_deadline', 'intermediate_phase_offer_submission_deadline', 'procurement_phase_documentation_download_deadline', 'procurement_phase_offer_submission_deadline', 'recommendation_resend_deadline', 'last_updated'];

    protected $guarded = [];
    public $incrementing = false; 

}
