{{--
    Componente: Modal de confirmación por contraseña para acciones sensibles.

    Uso en Blade:
        <x-confirm-password-modal
            action="/ruta/del/form"
            method="POST"
            title="Confirmar edición de precios"
            description="Esta acción requiere verificación de identidad."
            trigger-class="btn-editar-precios"
        >
            {{-- Campos extra del formulario si aplica --}}
        </x-confirm-password-modal>

    Uso con Alpine desde JS:
        window.dispatchEvent(new CustomEvent('open-confirm-password', {
            detail: { formId: 'mi-form-id' }
        }));
--}}

@props([
    'action'       => '#',
    'method'       => 'POST',
    'title'        => 'Confirmar acción',
    'description'  => 'Por seguridad, confirma tu contraseña para continuar.',
    'submitLabel'  => 'Confirmar',
    'formId'       => 'confirm-password-form-' . uniqid(),
])

<div x-data="confirmPasswordModal('{{ $formId }}')"
     @open-confirm-password.window="handleOpen($event.detail)">

    {{-- Trigger slot (opcional) --}}
    {{ $slot }}

    {{-- MODAL --}}
    <div x-show="open" x-transition.opacity
         class="fixed inset-0 z-[60] overflow-y-auto"
         style="display:none">

        <div class="fixed inset-0 bg-black/50" @click="cerrar()"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md"
                 @click.stop
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Header --}}
                <div class="flex items-start gap-4 px-6 pt-6 pb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-lock text-yellow-600"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $description }}</p>
                    </div>
                    <button type="button" @click="cerrar()"
                            class="ml-auto text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- Form --}}
                <form :id="formId"
                      :action="formAction"
                      method="POST"
                      @submit.prevent="enviar($el)">
                    @csrf
                    <div x-html="methodField"></div>

                    <div class="px-6 pb-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña actual
                        </label>
                        <div class="relative">
                            <input :type="mostrarPassword ? 'text' : 'password'"
                                   x-model="password"
                                   name="confirm_password"
                                   placeholder="Tu contraseña"
                                   autocomplete="current-password"
                                   class="w-full px-4 py-2.5 pr-10 border rounded-lg text-sm focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                                   :class="error ? 'border-red-400 bg-red-50' : 'border-gray-300'">
                            <button type="button" @click="mostrarPassword = !mostrarPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i :class="mostrarPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                            </button>
                        </div>
                        <p x-show="error" x-text="error"
                           class="text-xs text-red-600 mt-1.5"></p>
                    </div>

                    <div class="px-6 pb-6 mt-4 flex gap-3 justify-end">
                        <button type="button" @click="cerrar()"
                                class="px-4 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="cargando"
                                class="px-5 py-2 text-sm font-medium text-white bg-yellow-500 rounded-lg hover:bg-yellow-600 transition-colors disabled:opacity-60 flex items-center gap-2">
                            <i x-show="cargando" class="fas fa-spinner fa-spin text-xs"></i>
                            {{ $submitLabel }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmPasswordModal(defaultFormId) {
    return {
        open: false,
        formId: defaultFormId,
        formAction: '',
        methodField: '',
        password: '',
        error: '',
        cargando: false,
        mostrarPassword: false,

        handleOpen(detail) {
            this.formAction  = detail.action  ?? '';
            this.methodField = detail.method && detail.method !== 'POST'
                ? `<input type="hidden" name="_method" value="${detail.method}">`
                : '';
            this.formId   = detail.formId ?? defaultFormId;
            this.password = '';
            this.error    = '';
            this.open     = true;
            this.$nextTick(() => {
                document.querySelector(`#${this.formId} input[name=confirm_password]`)?.focus();
            });
        },

        cerrar() {
            this.open     = false;
            this.password = '';
            this.error    = '';
        },

        async enviar(form) {
            if (!this.password) {
                this.error = 'Ingresa tu contraseña para continuar.';
                return;
            }

            this.cargando = true;
            this.error    = '';

            try {
                const res = await fetch('/auth/verify-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ password: this.password }),
                });

                const data = await res.json();

                if (data.valid) {
                    // Contraseña correcta → enviar el formulario original
                    form.submit();
                } else {
                    this.error = 'Contraseña incorrecta. Inténtalo de nuevo.';
                }
            } catch (e) {
                this.error = 'Error de conexión. Inténtalo de nuevo.';
            } finally {
                this.cargando = false;
            }
        },
    };
}
</script>
