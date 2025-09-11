<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Crear tabla users
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->json('custom_fields')->nullable();
            $table->string('avatar_url')->nullable();
            $table->timestamps();
        });

        // Crear tabla password_reset_tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Crear tabla sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Crear tabla cache
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // Crear tabla cache_locks
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Crear tabla jobs
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Crear tabla job_batches
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // Crear tabla failed_jobs
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Crear tabla personal_access_tokens
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // Crear tablas de permisos (Spatie Permission)
        $this->createPermissionTables();

        // Crear tabla banks
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->timestamps();
        });

        // Crear tabla entities
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->enum('entity_type', ['Client', 'Supplier', 'Payroll', 'Cliente', 'Proveedor', 'Planilla']);
            $table->string('business_name', 255);
            $table->string('trade_name', 255)->nullable();
            $table->string('tax_id', 20)->unique();
            $table->string('business_group', 255)->nullable();
            $table->string('billing_email', 255)->nullable();
            $table->string('copy_email', 255)->nullable();
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->string('account_number', 50)->nullable();
            $table->string('interbank_account_number', 50)->nullable();
            $table->string('detraccion_account_number', 50)->nullable();
            $table->string('reference_recommendation', 255)->nullable();
            $table->integer('credit_days')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Crear tabla business_lines
        Schema::create('business_lines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Crear tabla projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('code', 50)->unique();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('business_line_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('category', ['Categoria1', 'Categoria2', 'Categoria3', 'Categoria4'])->nullable();
            $table->enum('validity', ['Vigente', 'Sin Vigencia'])->nullable()->comment('Vigencia para proyectos de tipo BOLSA DE HORAS');
            $table->enum('state', ['Activo', 'Inactivo', 'Completado', 'Suspendido'])->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('end_date_projected')->nullable();
            $table->date('end_date_real')->nullable();
            $table->decimal('real_progress', 5, 2)->nullable();
            $table->text('description_incidence')->nullable();
            $table->enum('reason_incidence', ['Clima', 'Falta de materiales', 'Problemas técnicos', 'Problemas administrativos', 'Otros'])->nullable();
            $table->text('description_risk')->nullable();
            $table->enum('state_risk', ['Alto', 'Medio', 'Bajo', 'Controlado'])->nullable();
            $table->text('description_change_control')->nullable();
            $table->decimal('billing', 5, 2)->nullable();
            $table->integer('delay_days')->nullable()->comment('Días laborables de desfase entre fecha de finalización planificada y fecha de finalización proyectada');
            $table->enum('phase', ['Inicio', 'Planificación', 'Ejecución', 'Control', 'Cierre'])->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Crear tabla project_user (tabla pivot)
        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });

        // Crear tabla project_milestones
        Schema::create('project_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('billing_percentage', 5, 2)->default(0);
            $table->enum('status', ['Pendiente', 'En Progreso', 'Completado', 'Retrasado'])->default('Pendiente');
            $table->decimal('progress', 3, 2)->default(0.00)->comment('Progreso del hito (0.00 a 1.00)');
            $table->boolean('is_paid')->default(false)->comment('Indica si el hito ha sido pagado');
            $table->integer('order')->default(1);
            $table->timestamps();
        });

        // Crear tabla billing_milestones
        Schema::create('billing_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('name', 255);
            $table->date('planned_date');
            $table->date('real_date')->nullable();
            $table->decimal('progress', 5, 2)->default(0); // 0.00 - 1.00 (0% - 100%)
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('status', 50);
            $table->text('comments')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Crear tabla equipments
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vehicle_type', 100);
            $table->foreignId('entity_id')->constrained()->onDelete('cascade')->onUpdate('no action');
            $table->string('driver');
            $table->string('license', 50);
            $table->string('plate_number1', 20);
            $table->string('plate_number2', 20)->nullable();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->onUpdate('no action');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->onUpdate('no action');
            $table->timestamps();
        });

        // Crear tabla equipment_logs
        Schema::create('equipment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipments')->onDelete('cascade')->onUpdate('no action');
            $table->date('date')->comment('Fecha');
            $table->decimal('diesel_gal', 8, 2)->nullable()->comment('Cantidad de Diesel en galones');
            $table->double('start_time')->nullable()->default(null)->comment('Hora de Inicio');
            $table->double('end_time')->nullable()->default(null)->comment('Hora de Fin');
            $table->double('engine_hours')->nullable()->default(null)->comment('Horas motor en trabajo');
            $table->double('delay_hours')->nullable()->default(null)->comment('Horas de demora');
            $table->enum('delay_activity', [
                'CALENTAMIENTO',
                'TRASLADO_EQUIPO',
                'MANTENIMIENTO_PREVIO',
                'MANTENIMIENTO_PROGRAMADO',
                'HORAS_MOTOR_MANTENIMIENTO',
                'HORAS_MOTOR_MANTENIMIENTO_NO_PROGRAMADO'
            ])->nullable()->default(null)->comment('Actividad de demora');
            $table->decimal('initial_mileage', 10, 2)->nullable()->comment('Kilometraje Inicial');
            $table->decimal('final_mileage', 10, 2)->nullable()->comment('Kilometraje Final');
            $table->decimal('tons', 8, 2)->nullable()->comment('Toneladas');
            $table->timestamps();
            $table->index('equipment_id');
        });

        // Crear tabla cost_centers
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->string('center_name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Crear tabla categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->string('category_name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->onDelete('set null');
        });

        // Crear tabla expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->enum('document_type', [
                'Recibo por Honorarios',
                'Recibo de Compra',
                'Nota de crédito',
                'Boleta de pago',
                'Nota de Pago',
                'Sin Documento',
                'Ticket'
            ]);
            $table->string('document_number', 50)->nullable();
            $table->date('document_date')->nullable();
            $table->unsignedBigInteger('cost_center_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('remark', 255)->nullable();
            $table->enum('currency', ['USD', 'PEN']);
            $table->decimal('amount_usd', 10, 2)->nullable();
            $table->decimal('amount_pen', 10, 2)->nullable();
            $table->decimal('exchange_rate', 10, 4)->nullable();
            $table->decimal('withholding_amount', 10, 2)->nullable();
            $table->enum('status', [
                'por revisar',
                'por pagar',
                'por pagar detraccion',
                'por reembolsar',
                'pagado'
            ]);
            $table->enum('payment_status', ['pendiente', 'pagado', 'anulado'])->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->date('planned_payment_date')->nullable();
            $table->date('actual_payment_date')->nullable();
            $table->enum('expense_type', ['fijo', 'variable'])->nullable();
            $table->decimal('amount_to_pay', 10, 2)->nullable();
            $table->unsignedBigInteger('responsible_id')->nullable();
            $table->boolean('has_attachment')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('accounting')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('entity_id')->references('id')->on('entities')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null')->onUpdate('cascade');
        });

        // Crear tabla incomes
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->enum('document_type', ['Boleta de venta', 'Factura', 'Nota de abono', 'Nota de débito', 'Valor residual']);
            $table->string('document_number', 50);
            $table->date('document_date');
            $table->string('description', 255)->nullable();
            $table->enum('currency', ['Soles', 'Dólares']);
            $table->decimal('amount_usd', 15, 2)->nullable();
            $table->decimal('amount_pen', 15, 2)->nullable();
            $table->date('payment_plan_date')->nullable();
            $table->date('real_payment_date')->nullable();
            $table->enum('status', ['Por Revisar', 'Por Facturar', 'Por Cobrar', 'Cobrado', 'Suspendido', 'Provisionado'])->default('Por Revisar');
            $table->decimal('service_percentage', 5, 2)->nullable();
            $table->decimal('deposit_amount', 15, 2)->nullable();
            $table->decimal('detraccion_amount', 15, 2)->nullable();
            $table->boolean('is_accounted')->default(false);
            $table->text('observations')->nullable();
            $table->string('attachment_path', 255)->nullable();
            $table->timestamps();
        });

        // Crear tabla sales_target_versions
        Schema::create('sales_target_versions', function (Blueprint $table) {
            $table->id();
            $table->integer('version_number');
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->integer('year');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->unique(['year', 'version_number']);
        });

        // Crear tabla sales_targets
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('sales_target_versions')->cascadeOnDelete();
            $table->foreignId('business_line_id')->constrained('business_lines')->cascadeOnDelete();
            $table->decimal('january_amount', 15, 2)->default(0);
            $table->decimal('february_amount', 15, 2)->default(0);
            $table->decimal('march_amount', 15, 2)->default(0);
            $table->decimal('april_amount', 15, 2)->default(0);
            $table->decimal('may_amount', 15, 2)->default(0);
            $table->decimal('june_amount', 15, 2)->default(0);
            $table->decimal('july_amount', 15, 2)->default(0);
            $table->decimal('august_amount', 15, 2)->default(0);
            $table->decimal('september_amount', 15, 2)->default(0);
            $table->decimal('october_amount', 15, 2)->default(0);
            $table->decimal('november_amount', 15, 2)->default(0);
            $table->decimal('december_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->unique(['version_id', 'business_line_id']);
        });

        // Crear tabla expense_budget_versions
        Schema::create('expense_budget_versions', function (Blueprint $table) {
            $table->id();
            $table->integer('version_number');
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->integer('year');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->unique(['year', 'version_number']);
        });

        // Crear tabla expense_budgets
        Schema::create('expense_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('expense_budget_versions')->cascadeOnDelete();
            $table->foreignId('cost_center_id')->constrained('cost_centers');
            $table->foreignId('category_id')->constrained('categories');
            $table->decimal('january_amount', 15, 2)->default(0);
            $table->decimal('february_amount', 15, 2)->default(0);
            $table->decimal('march_amount', 15, 2)->default(0);
            $table->decimal('april_amount', 15, 2)->default(0);
            $table->decimal('may_amount', 15, 2)->default(0);
            $table->decimal('june_amount', 15, 2)->default(0);
            $table->decimal('july_amount', 15, 2)->default(0);
            $table->decimal('august_amount', 15, 2)->default(0);
            $table->decimal('september_amount', 15, 2)->default(0);
            $table->decimal('october_amount', 15, 2)->default(0);
            $table->decimal('november_amount', 15, 2)->default(0);
            $table->decimal('december_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['version_id', 'cost_center_id', 'category_id']);
        });

        // Crear tabla time_entries
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('project_milestones')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->text('detail')->nullable()->comment('Detalles o comentarios sobre el registro de horas');
            $table->enum('phase', ['inicio', 'planificacion', 'ejecucion', 'control', 'cierre']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Crear tabla milestones (tabla vacía según la migración original)
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('milestones');
        Schema::dropIfExists('time_entries');
        Schema::dropIfExists('expense_budgets');
        Schema::dropIfExists('expense_budget_versions');
        Schema::dropIfExists('sales_targets');
        Schema::dropIfExists('sales_target_versions');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('equipment_logs');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('billing_milestones');
        Schema::dropIfExists('project_milestones');
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('business_lines');
        Schema::dropIfExists('entities');
        Schema::dropIfExists('banks');
        $this->dropPermissionTables();
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }

    /**
     * Crear las tablas de permisos de Spatie Permission
     */
    private function createPermissionTables(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }
        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new \Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        Schema::create($tableNames['permissions'], function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($teams, $columnNames) {
            $table->bigIncrements('id');
            if ($teams || config('permission.testing')) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key'], 'roles_team_foreign_key_index');
            }
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();
            if ($teams || config('permission.testing')) {
                $table->unique([$columnNames['team_foreign_key'], 'name', 'guard_name']);
            } else {
                $table->unique(['name', 'guard_name']);
            }
        });

        Schema::create($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger($pivotPermission);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_permissions_team_foreign_key_index');
                $table->primary([$columnNames['team_foreign_key'], $pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            } else {
                $table->primary([$pivotPermission, $columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_permission_model_type_primary');
            }
        });

        Schema::create($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $columnNames, $pivotRole, $teams) {
            $table->unsignedBigInteger($pivotRole);
            $table->string('model_type');
            $table->unsignedBigInteger($columnNames['model_morph_key']);
            $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->onDelete('cascade');
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key']);
                $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');
                $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
            } else {
                $table->primary([$pivotRole, $columnNames['model_morph_key'], 'model_type'], 'model_has_roles_role_model_type_primary');
            }
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames, $pivotRole, $pivotPermission) {
            $table->unsignedBigInteger($pivotPermission);
            $table->unsignedBigInteger($pivotRole);
            $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->onDelete('cascade');
            $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->onDelete('cascade');
            $table->primary([$pivotPermission, $pivotRole], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Eliminar las tablas de permisos de Spatie Permission
     */
    private function dropPermissionTables(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};