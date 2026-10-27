<div
    <?php echo e($attributes
            ->merge([
                'id' => $getId(),
            ], escape: false)
            ->merge($getExtraAttributes(), escape: false)); ?>

>
    <?php echo e($getChildSchema()); ?>

</div>
<?php /**PATH D:\ai_school_new-main (1)\ai_school_new-main\vendor\filament\schemas\resources\views/components/grid.blade.php ENDPATH**/ ?>