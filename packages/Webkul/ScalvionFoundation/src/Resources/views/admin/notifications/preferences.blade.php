<x-admin::layouts>
    <x-slot:title>
        Notification Preferences
    </x-slot>

    <x-admin::form method="POST" :action="route('admin.scalvion.notifications.preferences.store')">
        <div class="flex flex-col gap-4">
            <div class="scroll-reactive-sticky sticky top-[60px] z-[1000] flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                <div>
                    <h1 class="text-xl font-bold dark:text-white">Notification Preferences</h1>
                    <p class="text-sm text-gray-500">Control reminder, audit, activity, and WhatsApp alerts.</p>
                </div>

                <button type="submit" class="primary-button">Save</button>
            </div>

            <div class="rounded-lg border border-gray-300 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="grid gap-4">
                    <label class="inline-flex items-center gap-3">
                        <input type="hidden" name="reminder_notifications" value="0">
                        <input type="checkbox" name="reminder_notifications" value="1" @checked($preference->reminder_notifications)>
                        <span class="dark:text-white">Reminder notifications</span>
                    </label>

                    <label class="inline-flex items-center gap-3">
                        <input type="hidden" name="activity_notifications" value="0">
                        <input type="checkbox" name="activity_notifications" value="1" @checked($preference->activity_notifications)>
                        <span class="dark:text-white">Activity notifications</span>
                    </label>

                    <label class="inline-flex items-center gap-3">
                        <input type="hidden" name="audit_notifications" value="0">
                        <input type="checkbox" name="audit_notifications" value="1" @checked($preference->audit_notifications)>
                        <span class="dark:text-white">Audit notifications</span>
                    </label>

                    <label class="inline-flex items-center gap-3">
                        <input type="hidden" name="whatsapp_notifications" value="0">
                        <input type="checkbox" name="whatsapp_notifications" value="1" @checked($preference->whatsapp_notifications)>
                        <span class="dark:text-white">WhatsApp notifications</span>
                    </label>
                </div>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>
