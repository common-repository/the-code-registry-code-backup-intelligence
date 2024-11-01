<script setup>
import { getCurrentInstance, ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const { proxy } = getCurrentInstance()

const props = defineProps({
    context: String
});

const showModal = ref(false);

function openChangesOverTimeModal() {
    showModal.value = true;
}
</script>

<template>
    <button class="flex items-center rounded bg-white px-2 py-1 text-xs font-semibold text-black shadow-sm hover:bg-gray-300" @click="openChangesOverTimeModal">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 inline">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6" />
        </svg>
        <span class="sm:leading-[14px]">How has this changed over time?</span>
    </button>
    <TransitionRoot as="template" :show="showModal">
        <Dialog class="relative z-1000" @close="showModal = false">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 pb-20 text-center sm:items-center sm:p-0 sm:pb-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl sm:p-6">
                            <div class="mt-3 text-center sm:mt-5">
                                <DialogTitle as="h3" class="font-serif text-xl font-semibold leading-6 text-brand-blue">
                                    Show Changes Over Time - view this in our main web app
                                </DialogTitle>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">This widget, as well as our comparison reports, show you how important data points in your code have changed over time. Including security and coding quality issues and outdated third party components.</p>
                                    <img :src="proxy.$wpData.pluginUrl + 'admin/img/screenshots/overtime.png'" alt="Feature preview" class="mt-5 mb-10 w-full " />
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">This and many more features are exclusive to our main web app, including live chat with our AI assistant Ada, full security issue triaging, exporting SBOMS of your components and license and much more.</p>
                                <p class="mt-2 text-sm text-gray-500">If you're here you already have an account! Simply login with the same email you used when you setup this plugin.</p> 
                            </div>
                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                <a href="https://app.thecoderegistry.com" target="_blank" class="inline-flex w-full justify-center rounded-md bg-brand-purple px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-blue hover:text-white focus:outline-none focus:text-white sm:col-start-2">Open the web app</a>
                                <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0" @click="showModal = false" ref="cancelButtonRef">Cancel</button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>