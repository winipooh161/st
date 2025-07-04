

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
           <div class="text-center mb-4">
                    <div class="avatar-upload-container position-relative mx-auto" style="">
                        <img id="profile-avatar-preview"
                            src="<?php echo e(Auth::user()->avatar ? asset('storage/avatars/'.Auth::user()->avatar) : asset('images/default-avatar.jpg')); ?>"
                            class="profile-avatar  w-100 h-100"
                            alt="Аватар пользователя"
                            style="object-fit: cover;">
                    </div>
                </div>
            <!-- Сообщения об успехе/ошибке -->
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
               
                <div class="card-body">
                    <?php if($userTemplates->count() > 0): ?>
                        <div class="instagram-grid">
                            <div class="row">
                                <?php $__currentLoopData = $userTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userTemplate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4 ">
                                    <div class="reel-card">
                                        <!-- Ссылка на всю карточку -->
                                        <a href="<?php echo e(route('user-templates.show', $userTemplate)); ?>" class="card-link-overlay"></a>
                                        <div class="reel-content">
                                            <!-- Превью шаблона с обложкой -->
                                            <?php if($userTemplate->cover_path): ?>
                                                <?php if($userTemplate->cover_type == 'image'): ?>
                                                    <img src="<?php echo e(Storage::url($userTemplate->cover_path)); ?>" 
                                                         alt="<?php echo e($userTemplate->name); ?>" 
                                                         class="img-fluid w-100 h-100 object-fit-cover">
                                                <?php elseif($userTemplate->cover_type == 'video'): ?>
                                                    <video class="w-100 h-100 object-fit-cover" autoplay loop muted playsinline>
                                                        <source src="<?php echo e(Storage::url($userTemplate->cover_path)); ?>" type="video/mp4">
                                                    </video>
                                                    <?php if($userTemplate->cover_thumbnail): ?>
                                                        <img src="<?php echo e(Storage::url($userTemplate->cover_thumbnail)); ?>" 
                                                             alt="<?php echo e($userTemplate->name); ?>" 
                                                             class="thumbnail-preview">
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            <?php elseif($userTemplate->preview_image): ?>
                                                <img src="<?php echo e(asset('storage/' . $userTemplate->preview_image)); ?>" 
                                                     alt="<?php echo e($userTemplate->name); ?>" 
                                                     class="img-fluid">
                                            <?php else: ?>
                                                <div class="placeholder-content">
                                                    <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                                                    <h6 class="mt-2 text-muted"><?php echo e($userTemplate->name); ?></h6>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark-plus display-1 text-muted"></i>
                            <h5 class="mt-3">У вас пока нет шаблонов</h5>
                            <p class="text-muted mb-4">Создайте свой первый шаблон, чтобы начать работу</p>
                            <a href="<?php echo e(route('templates.create')); ?>" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>Создать первый шаблон
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>



<style>
    .card-body {
        padding: 0
    }
    .instagram-grid {
        width: 100%;
    }
    
    .reel-card {
        width: 100%;
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .reel-card:before {
        content: "";
        display: block;
        padding-top: 177.8%; /* Соотношение сторон 9:16 для формата Reels */
    }
    
    .reel-card:hover {
        transform: translateY(-5px);
    }
    
    .reel-content {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 1px solid #e0e0e0;
    }
    
    .placeholder-content {
        font-size: 1.2rem;
        color: #6c757d;
        text-align: center;
    }
    
    /* Стили для ссылки на всю карточку */
    .card-link-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1;
    }

    /* Для того чтобы карточка выглядела кликабельной */
    .reel-card {
        cursor: pointer;
    }
    
    @media (max-width: 768px) {
        .col-md-4 {
            padding: 0 0px;
        }
        
        .instagram-grid .row {
            margin: 0 0px;
        }
    }
        .col-md-4 {
        flex: 0 0 auto;
        width: 33.33333333%;
        padding: 0; margin: 0;
    }
    
    .object-fit-cover {
        object-fit: cover;
    }
    
    .thumbnail-preview {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: -1;
        opacity: 0.5;
    }
    
    video.object-fit-cover {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
</style>


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\tyty\resources\views/my-templates.blade.php ENDPATH**/ ?>