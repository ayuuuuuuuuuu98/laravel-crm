<x-admin::layouts>
    <x-slot:title>
        {{ __('whatsapp::app.settings.title') }}
    </x-slot:title>

    <div class="flex flex-col gap-4">
        <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
            <div class="flex flex-col gap-2">
                <x-admin::breadcrumbs name="settings.whatsapp" />

                <div class="text-xl font-bold dark:text-white">
                    {{ __('whatsapp::app.settings.title') }}
                </div>
            </div>

            <button type="submit" form="whatsapp-settings-form" class="primary-button">
                {{ __('admin::app.settings.save-btn') }}
            </button>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <form id="whatsapp-settings-form" method="post" action="{{ route('admin.whatsapp.settings.store') }}">
                @csrf

                <input type="hidden" name="id" value="{{ $account?->id ?? '' }}">

                <div class="grid gap-4 lg:grid-cols-2">
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label for="meta_app_id">
                            {{ __('whatsapp::app.settings.meta_app_id') }}
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="meta_app_id"
                            id="meta_app_id"
                            :value="old('meta_app_id', $account?->meta_app_id)"
                            :label="trans('whatsapp::app.settings.meta_app_id')"
                            class="w-full"
                        />
                        <x-admin::form.control-group.error control-name="meta_app_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label for="meta_app_secret">
                            {{ __('whatsapp::app.settings.meta_app_secret') }}
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="password"
                            name="meta_app_secret"
                            id="meta_app_secret"
                            :value="old('meta_app_secret', $account?->meta_app_secret)"
                            :label="trans('whatsapp::app.settings.meta_app_secret')"
                            class="w-full"
                        />
                        <x-admin::form.control-group.error control-name="meta_app_secret" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label for="business_account_id">
                            {{ __('whatsapp::app.settings.business_account_id') }}
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="business_account_id"
                            id="business_account_id"
                            :value="old('business_account_id', $account?->business_account_id)"
                            :label="trans('whatsapp::app.settings.business_account_id')"
                            class="w-full"
                        />
                        <x-admin::form.control-group.error control-name="business_account_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label for="phone_number_id">
                            {{ __('whatsapp::app.settings.phone_number_id') }}
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="phone_number_id"
                            id="phone_number_id"
                            :value="old('phone_number_id', $account?->phone_number_id)"
                            :label="trans('whatsapp::app.settings.phone_number_id')"
                            class="w-full"
                        />
                        <x-admin::form.control-group.error control-name="phone_number_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label for="access_token">
                            {{ __('whatsapp::app.settings.access_token') }}
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="password"
                            name="access_token"
                            id="access_token"
                            :value="old('access_token', $account?->access_token)"
                            :label="trans('whatsapp::app.settings.access_token')"
                            class="w-full"
                        />
                        <x-admin::form.control-group.error control-name="access_token" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label for="verify_token">
                            {{ __('whatsapp::app.settings.verify_token') }}
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="text"
                            name="verify_token"
                            id="verify_token"
                            :value="old('verify_token', $account?->verify_token)"
                            :label="trans('whatsapp::app.settings.verify_token')"
                            class="w-full"
                        />
                        <x-admin::form.control-group.error control-name="verify_token" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group class="lg:col-span-2">
                        <x-admin::form.control-group.label for="webhook_url">
                            {{ __('whatsapp::app.settings.webhook_url') }}
                        </x-admin::form.control-group.label>
                        <div class="rounded border border-gray-300 bg-gray-100 p-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                            {{ $webhookUrl }}
                        </div>
                    </x-admin::form.control-group>

                    <x-admin::form.control-group class="lg:col-span-2">
                        <x-admin::form.control-group.label for="status">
                            {{ __('whatsapp::app.settings.status') }}
                        </x-admin::form.control-group.label>
                        <x-admin::form.control-group.control
                            type="select"
                            name="status"
                            id="status"
                            :label="trans('whatsapp::app.settings.status')"
                            class="w-full"
                        >
                            <option value="active" {{ old('status', $account?->status) === 'active' ? 'selected' : '' }}>{{ __('whatsapp::app.settings.status_active') }}</option>
                            <option value="inactive" {{ old('status', $account?->status) === 'inactive' ? 'selected' : '' }}>{{ __('whatsapp::app.settings.status_inactive') }}</option>
                        </x-admin::form.control-group.control>
                        <x-admin::form.control-group.error control-name="status" />
                    </x-admin::form.control-group>
                </div>
            </form>
        </div>
    </div>
</x-admin::layouts>
