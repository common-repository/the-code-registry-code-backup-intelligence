<script setup>
import { getCurrentInstance } from 'vue'
import { QuestionMarkCircleIcon, ClockIcon, ShieldCheckIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'

const emit = defineEmits(['changeView'])

const { proxy } = getCurrentInstance()

const questions = [
  {
    name: 'How long does it take?',
    description:
      'It depends on the size of your site\'s code (i.e. how many plugins and custom code you\'ve got), but it can be as quick as <span class="text-brand-purple font-semibold">5 minutes</span>!',
    icon: ClockIcon,
  },
  {
    name: 'Is it secure?',
    description:
      'Very! Everything we\'ve built has been built with security in mind from day one. <a href="https://thecoderegistry.com/how-secure-is-the-code-registry/" target="_blank" class="text-brand-purple font-semibold hover:text-brand-blue">You can read more about our security here</a>.',
    icon: ShieldCheckIcon,
  },
  {
    name: 'What will you be able to tell me about my code?',
    description:
      'A lot: Programming languages, file types, security vulnerabilities, third party dependencies, code quality issues, open source licenses, code complexity, how much it could cost to replicate your code, and more!',
    icon: QuestionMarkCircleIcon,
  },
  {
    name: 'Who are The Code Registry?',
    description:
      'We are a US and UK based team building the first tool designed for non-developers. We exist to put the power back into the hands of business owners, Digital Leaders, Investors and Product Owners who want to improve their control and ownership of their code\'s IP and Development Roadmap.', 
    icon: InformationCircleIcon,
  },
]

const startSignup = () => {
  emit('changeView', 'Signup')
}
</script>

<template>
  <div class="relative overflow-x-hidden overflow-y-hidden isolate pt-10">
    <img class="absolute z-[-1] w-[400px] -top-[90px] -right-[130px] opacity-25 lg:opacity-50 2xl:w-[640px] 2xl:-top-[160px] 2xl:-right-[220px]" :src="`${proxy.$wpData.pluginUrl}admin/img/angled-icon.svg`" alt="The Code Registry angled icon">
    <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
      <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
    </div>
    <div class="py-24 sm:py-32 lg:pb-40">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto text-center">
          <img class="inline mx-auto mb-10 h-12 w-auto" src="https://thecoderegistry.com/wp-content/uploads/2023/12/CR_POS_HOR@2x.png" alt="" />
          <h1 class="font-serif text-4xl tracking-tight text-brand-blue sm:text-5xl">Backup &amp; analyze your WordPress site's code to find security vulnerabilities, code quality issues, outdated dependencies, and more.</h1>
          <p class="mt-6 text-xl leading-8 text-black">The Code Intelligence plugin securely packages your WordPress site's code (including custom themes, plugins and WordPress itself) and sends it to The Code Registry's AI-powered code intelligence platform. We show our findings (along with AI insights) on a modern, responsive dashboard that's easy to use and understand.</p>
          <p class="mt-6 text-xl leading-8 text-black">The results can be ready in as quick as <span class="text-brand-purple font-semibold">5 minutes</span> and are shown right here in your WordPress dashboard - you never need to leave your site!</p>
          <div class="mt-10 flex items-center justify-center gap-x-6">
            <button type="button" class="rounded-md bg-brand-purple px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-blue hover:text-white" @click="startSignup">Get started for free!</button>
            <a href="#faqs" class="text-sm font-semibold leading-6 text-gray-900">Learn more <span aria-hidden="true">→</span></a>
          </div>
        </div>
        <div class="mt-16 flow-root sm:mt-24">
          <div class="-m-2 rounded-xl bg-gray-900/5 p-2 ring-1 ring-inset ring-gray-900/10 lg:-m-4 lg:rounded-2xl lg:p-4">
            <img :src="`${proxy.$wpData.pluginUrl}admin/img/preview-screenshot.png`" alt="App screenshot" width="2432" height="1442" class="rounded-md shadow-2xl ring-1 ring-gray-900/10" />
          </div>
        </div>
        <a name="faqs"></a>
        <div class="mx-auto mt-24 max-w-2xl lg:text-center">
          <h2 class="text-base font-semibold leading-7 text-indigo-600">Frequently Asked Questions</h2>
          <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">How quick is it? How secure is it?</p>
          <p class="mt-6 text-lg leading-8 text-gray-600">We've answered some of your questions below and we have a whole lot more information on our dedicated website!</p>
          <div class="mt-10 flex items-center justify-center gap-x-6">
            <button type="button" class="rounded-md bg-brand-purple px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-blue hover:text-white" @click="startSignup">Get started for free!</button>
            <a href="https://thecoderegistry.com" target="_blank" class="text-sm font-semibold leading-6 text-gray-900">Read more on our website <span aria-hidden="true">→</span></a>
          </div>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
          <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
            <div v-for="question in questions" :key="question.name" class="relative pl-16">
              <dt class="text-base font-semibold leading-7 text-gray-900">
                <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-brand-purple">
                  <component :is="question.icon" class="h-6 w-6 text-white" aria-hidden="true" />
                </div>
                {{ question.name }}
              </dt>
              <dd class="mt-2 text-base leading-7 text-gray-600" v-html="question.description"></dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
    <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
      <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
    </div>
  </div>
</template>