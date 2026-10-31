<?php ($label = $label ?? 'élément(s)'); ?>
<style>
.pass-pagination { width: 100%; }
.pass-pagination-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem;
}
.pass-pagination-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    font-size: 0.78rem;
    color: #8b8b98;
}
.pass-pagination-btns {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.pass-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.42rem 0.95rem;
    border: 1px solid #e0dde3;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #3b3b45;
    background: #fff;
    text-decoration: none;
    line-height: 1;
    transition: all 0.15s ease;
}
.pass-btn:hover {
    border-color: var(--violet, #542680);
    color: var(--violet, #542680);
    background: #f8f6f9;
}
.pass-btn-disabled {
    opacity: 0.45;
    pointer-events: none;
}
.pass-pagination-foot {
    display: flex;
    justify-content: flex-end;
    margin-top: 0.65rem;
}
.pass-per-page {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.78rem;
    color: #8b8b98;
    margin: 0;
}
.pass-per-page select {
    padding: 0.3rem 0.55rem;
    border: 1px solid #e0dde3;
    border-radius: 7px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #3b3b45;
    background: #fff;
    cursor: pointer;
}
.pass-per-page select:focus {
    outline: none;
    border-color: var(--violet, #542680);
    box-shadow: 0 0 0 0.15rem rgba(84, 38, 128, 0.12);
}
</style>

<div class="pass-pagination">
    <div class="pass-pagination-row">
        <span class="pass-pagination-meta">
            Page <?php echo e($paginator->currentPage()); ?> sur <?php echo e($paginator->lastPage()); ?> — <?php echo e($paginator->total()); ?> <?php echo e($label); ?>

        </span>

        <?php if($paginator->hasPages()): ?>
            <div class="pass-pagination-btns">
                <?php if($paginator->onFirstPage()): ?>
                    <span class="pass-btn pass-btn-disabled">Précédent</span>
                <?php else: ?>
                    <a class="pass-btn" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev">Précédent</a>
                <?php endif; ?>

                <?php if($paginator->hasMorePages()): ?>
                    <a class="pass-btn" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next">Suivant</a>
                <?php else: ?>
                    <span class="pass-btn pass-btn-disabled">Suivant</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="pass-pagination-foot">
        <form method="GET" action="<?php echo e(url()->current()); ?>" class="pass-per-page">
            <?php $__currentLoopData = request()->except(['page', 'per_page']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(is_array($value)): ?>
                    <?php $__currentLoopData = $value; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <input type="hidden" name="<?php echo e($key); ?>[]" value="<?php echo e($v); ?>">
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>">
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <label for="per_page">Afficher</label>
            <select id="per_page" name="per_page" onchange="this.form.submit()">
                <?php $__currentLoopData = \App\Support\PerPage::ALLOWED; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($n); ?>" <?php if($n === $paginator->perPage()): echo 'selected'; endif; ?>><?php echo e($n); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <span>par page</span>
        </form>
    </div>
</div>
<?php /**PATH /home/abel/Projects/PassEvent/resources/views/vendor/pagination/pass.blade.php ENDPATH**/ ?>