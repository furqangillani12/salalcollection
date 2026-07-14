<?php if (isset($component)) { $__componentOriginal69dc84650370d1d4dc1b42d016d7226b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b = $attributes; } ?>
<?php $component = App\View\Components\GuestLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('guest-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\GuestLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>

    <div class="text-center mb-6">
        <h2 class="text-2xl font-extrabold text-gray-900">Welcome back</h2>
        <p class="text-sm text-gray-500 mt-1">Sign in to continue to your dashboard</p>
    </div>

    
    <?php if(session('status')): ?>
        <div class="mb-4 p-3 rounded-lg text-sm border"
             style="background:#ecfdf5;border-color:#a7f3d0;color:#047857;">
            <i class="fas fa-circle-info mr-1"></i> <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?>

    
    <?php if($errors->any()): ?>
        <div class="mb-4 p-3 rounded-lg text-sm border"
             style="background:#fef2f2;border-color:#fecaca;color:#b91c1c;">
            <ul class="list-disc list-inside space-y-0.5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($err); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login')); ?>"
          x-data="{ showPwd:false }" class="space-y-4">
        <?php echo csrf_field(); ?>

        
        <div>
            <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5">
                Email address
            </label>
            <div style="position:relative;">
                <i class="fas fa-envelope text-gray-400"
                   style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;font-size:13px;"></i>
                <input id="email" name="email" type="email" required autofocus autocomplete="username"
                       value="<?php echo e(old('email')); ?>"
                       style="padding-left:40px;"
                       class="field-input"
                       placeholder="you@example.com">
            </div>
        </div>

        
        <div>
            <label for="password" class="block text-xs font-semibold text-gray-700 mb-1.5">
                Password
            </label>
            <div style="position:relative;">
                <i class="fas fa-lock text-gray-400"
                   style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;font-size:13px;"></i>
                <input id="password" name="password" required autocomplete="current-password"
                       :type="showPwd ? 'text' : 'password'"
                       style="padding-left:40px;padding-right:42px;"
                       class="field-input"
                       placeholder="Enter your password">
                <button type="button" @click="showPwd = !showPwd" tabindex="-1"
                        class="text-gray-400 hover:text-gray-700"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:13px;">
                    <i class="fas" :class="showPwd ? 'fa-eye-slash' : 'fa-eye'"></i>
                </button>
            </div>
        </div>

        
        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
            <input id="remember_me" name="remember" type="checkbox"
                   class="rounded border-gray-300 text-cyan-600 shadow-sm focus:ring-cyan-500">
            <span class="ms-2 text-sm text-gray-600">Remember me on this device</span>
        </label>

        
        <button type="submit" class="btn-primary w-full inline-flex items-center justify-center gap-2 mt-2">
            <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
    </form>

    
    <p class="text-center text-xs text-gray-400 mt-5">
        <i class="fas fa-shield-halved mr-1"></i> Secure connection · branch-aware access
    </p>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $attributes = $__attributesOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__attributesOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b)): ?>
<?php $component = $__componentOriginal69dc84650370d1d4dc1b42d016d7226b; ?>
<?php unset($__componentOriginal69dc84650370d1d4dc1b42d016d7226b); ?>
<?php endif; ?>
<?php /**PATH /Users/mhtech/Documents/Practive-Laravel-Projects/salalcollection/resources/views/auth/login.blade.php ENDPATH**/ ?>