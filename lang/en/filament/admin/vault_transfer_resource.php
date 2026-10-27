<?php

return [
    'navigation_label' => 'Vault Transfers',
    'model_label' => 'Transfer',
    'plural_model_label' => 'Vault Transfers',
    'navigation_group' => 'Financial Management',

    // Fields
    'transfer_code' => 'Transfer Code',
    'transfer_date' => 'Transfer Date',
    'from_vault_id' => 'From Vault',
    'to_vault_id' => 'To Vault',
    'amount' => 'Amount',
    'status' => 'Status',
    'notes' => 'Notes',
    'cancellation_reason' => 'Cancellation Reason',
    'transferred_by' => 'Transferred By',
    'approved_by' => 'Approved By',
    'approved_at' => 'Approved At',

    // Status
    'status.pending' => 'Pending',
    'status.completed' => 'Completed',
    'status.cancelled' => 'Cancelled',

    // Messages
    'created_successfully' => 'Transfer created successfully',
    'updated_successfully' => 'Transfer updated successfully',
    'deleted_successfully' => 'Transfer deleted successfully',
    'approved_successfully' => 'Transfer approved successfully',
    'cancelled_successfully' => 'Transfer cancelled successfully',

    // Errors
    'cannot_edit' => 'This transfer cannot be edited',
    'cannot_delete' => 'This transfer cannot be deleted',
    'same_vault_error' => 'Cannot transfer from and to the same vault',
];
