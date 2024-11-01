<script setup>
import { ref, getCurrentInstance } from 'vue'

const emit = defineEmits(['changeView'])
const { proxy } = getCurrentInstance()

const formData = ref({
  name: proxy.$wpData.userProfileName,
  email: proxy.$wpData.userProfileEmail,
  team_name: proxy.$wpData.siteName,
  source: 'wordpress',
  account_package_identifier: 'basic'
})

const isLoading = ref(false)
const errorMessage = ref('')
const verificationRequired = ref(false)
const verificationCode = ref('')

const handleSubmit = async (event) => {
  event.preventDefault()
  isLoading.value = true
  errorMessage.value = ''

  try {
    const response = await fetch(proxy.$wpData.ajaxUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'tcr_cbi_create_account',
        nonce: proxy.$wpData.nonce,
        ...formData.value,
        ...(verificationRequired.value ? { two_factor_code: verificationCode.value } : {})
      })
    })

    const data = await response.json()

    if (response.ok) {
      if (data.success) {
        if (data.data.verification_required) {
          verificationRequired.value = true
          errorMessage.value = data.data.message
        } else if (data.data.payment_url) {
          // Redirect to payment URL
          window.location.href = data.data.payment_url
        }
      } else {
        errorMessage.value = data.data.message || 'An error occurred. Please try again.'
      }
    } else {
      errorMessage.value = data.data?.message || 'An error occurred. Please try again.'
    }
  } catch (error) {
    console.error('Error:', error)
    errorMessage.value = 'A network error occurred. Please check your internet connection and try again.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="relative overflow-x-hidden overflow-y-hidden isolate bg-white px-6 py-24 sm:py-32 lg:px-8">
    <img class="absolute z-[-1] w-[400px] -top-[90px] -right-[130px] opacity-25 lg:opacity-50 2xl:w-[640px] 2xl:-top-[160px] 2xl:-right-[220px]" :src="`${proxy.$wpData.pluginUrl}admin/img/angled-icon.svg`" alt="The Code Registry angled icon">
    <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
      <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
    </div>
    <div class="mx-auto max-w-xl lg:max-w-4xl">
      <img class="block mb-10 h-12 w-auto" src="https://thecoderegistry.com/wp-content/uploads/2023/12/CR_POS_HOR@2x.png" alt="" />
      <h2 class="font-serif text-4xl tracking-tight text-brand-blue">Try our <span class="text-brand-purple">code intelligence and insights</span> platform <span class="text-brand-purple">for free</span> with a <span class="text-brand-purple">2 week free trial</span> (no payment info required).</h2>
      <p class="mt-2 text-lg leading-8 text-black">We just need a few bits of information from you to get started. You'll then get taken to our secure Stripe checkout page to stary your free trial, but you won't need to enter any payment information.</p>
      <div class="mt-10 flex flex-col gap-16 sm:gap-y-20 lg:flex-row">
        <form @submit="handleSubmit" class="lg:flex-auto">
          <div class="grid grid-cols-1 gap-x-8 gap-y-6 sm:grid-cols-2">
            <div>
              <label for="name" class="block text-sm font-semibold leading-6 text-gray-900">Your name</label>
              <div class="mt-2.5">
                <input type="text" name="name" id="name" autocomplete="name" v-model="formData.name" class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-purple sm:text-sm sm:leading-6" required />
              </div>
            </div>
            <div>
              <label for="email" class="block text-sm font-semibold leading-6 text-gray-900">Your email</label>
              <div class="mt-2.5">
                <input type="email" name="email" id="email" autocomplete="email" v-model="formData.email" class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-purple sm:text-sm sm:leading-6" required />
              </div>
            </div>
          </div>
          <div v-if="verificationRequired" class="mt-6">
            <label for="verification-code" class="block text-sm font-semibold leading-6 text-gray-900">Verification Code</label>
            <div class="mt-2.5">
              <input type="text" name="verification-code" id="verification-code" v-model="verificationCode" class="block w-full rounded-md border-0 px-3.5 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-purple sm:text-sm sm:leading-6" required />
            </div>
            <div class="mt-2 p-2 bg-orange-100 border border-orange-400 text-orange-900 rounded">
              {{ errorMessage }}
            </div>
          </div>
          <div class="mt-10">
            <button type="submit" :disabled="isLoading" class="block w-full rounded-md bg-brand-purple px-3.5 py-2.5 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-purple" :class="{ 'animate-pulse': isLoading }">
              {{ isLoading ? 'Processing...' : (verificationRequired ? 'Verify Account' : 'Sign up to 2 week free trial') }}
            </button>
          </div>
          <div v-if="errorMessage && !verificationRequired" class="mt-2 p-2 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ errorMessage }}
          </div>
          <p class="mt-4 text-sm leading-6 text-gray-500">By submitting this form, I agree to the <a href="https://thecoderegistry.com/privacy-policy/" target="_blank" class="font-semibold text-brand-purple">privacy&nbsp;policy</a>.</p>
        </form>
        <div class="lg:w-80 lg:flex-none">
          <figure>
            <blockquote class="text-lg p-0 bg-transparent font-semibold leading-8 text-gray-900">
              <p class="text-lg">"The Code Registry is a great solution for CTO's and CEO's wanting to share and better understand their code"</p>
            </blockquote>
            <figcaption class="mt-10 flex gap-x-6">
              <img src="https://thecoderegistry.com/wp-content/uploads/2023/12/Alex-Zhitomirskey-CEO-valex.webp" alt="" class="h-12 w-12 flex-none rounded-full bg-gray-50" />
              <div>
                <div class="text-base font-semibold text-gray-900">Alex Zhitomirskey</div>
                <div class="text-sm leading-6 text-gray-600">CEO, Valex Consulting</div>
              </div>
            </figcaption>
          </figure>
        </div>
      </div>
    </div>
    <div class="mx-auto max-w-xl lg:max-w-4xl mt-20 px-6 lg:px-8">
      <div class="mx-auto max-w-2xl lg:text-center">
        <h2 class="text-base font-semibold leading-7 text-brand-purple">How does it work? What happens next?</h2>
        <p class="mt-2 text-3xl font-bold tracking-tight text-black sm:text-4xl">It's a VERY simple 3 step process.</p>
        <p class="mt-6 text-lg leading-8 text-gray-600">You don't need to know anything about coding or have technical expertise to benefit from our AI-powered code insights and intelligence.</p>
      </div>
      <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
        <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3">
          <div class="flex flex-col">
            <dt class="flex items-center gap-x-3 font-semibold leading-7 text-black">
              <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-purple text-white text-semibold">1</span>
              Sign up for an account
            </dt>
            <dd class="mt-4 flex flex-auto flex-col leading-7 text-gray-600">
              <p>Simply sign up above for a free 2 week trial with just your name and email address.</p>
              <p class="mt-2">You'll need to verify your email address using a code we send you.</p>
            </dd>
          </div>
          <div class="flex flex-col">
            <dt class="flex items-center gap-x-3 font-semibold leading-7 text-black">
              <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-purple text-white text-semibold">2</span>
              We archive your site's code
            </dt>
            <dd class="mt-4 flex flex-auto flex-col leading-7 text-gray-600">
              <p>We automatically create a secure, encrypted archive of your WordPress site's code, including themes, plugins and core WordPress code.</p>
              <p class="mt-2">You don't need to do anything here, we do it for you.</p>
            </dd>
          </div>
          <div class="flex flex-col">
            <dt class="flex items-center gap-x-3 font-semibold leading-7 text-black">
              <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-purple text-white text-semibold">3</span>
              We show you our findings
            </dt>
            <dd class="mt-4 flex flex-auto flex-col leading-7 text-gray-600">
              <p>You view our findings directly within your WordPress dashboard, including security vulnerabilities, code complexity, coding quality, outdated third party dependencies and more.</p>
              <p class="mt-2">You'll also receive a PDF report of our finings via email, and you'll be able to access our main web app directly which has many more features.</p>
            </dd>
          </div>
        </dl>
      </div>
    </div>
    <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]" aria-hidden="true">
      <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem]" style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)" />
    </div>
  </div>
</template>