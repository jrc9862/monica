<script setup>
import { ref } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import JetBanner from '@/Components/Jetstream/Banner.vue';
import JetDropdown from '@/Components/Dropdown.vue';
import JetDropdownLink from '@/Components/DropdownLink.vue';
import JetNavLink from '@/Components/NavLink.vue';
import JetResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import FooterLayout from '@/Layouts/FooterLayout.vue';

defineProps({
  title: String,
});

const showingNavigationDropdown = ref(false);

const logout = () => {
  router.post(route('logout'));
};
</script>

<template>
  <div>
    <Head :title="title" />

    <JetBanner />

    <div class="flex min-h-screen flex-col bg-bg dark:bg-surface">
      <nav class="border-b border-border bg-surface dark:border-border dark:bg-surface">
        <!-- Primary Navigation Menu -->
        <div class="mx-auto max-w-5xl px-3 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <div class="flex">
              <!-- Navigation Links -->
              <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <JetNavLink :href="route('vault.index')" :active="route().current('vault.index')">
                  {{ $t('Home') }}
                </JetNavLink>
              </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
              <!-- Settings Dropdown -->
              <div class="relative ms-3">
                <JetDropdown align="right" width="48">
                  <template #trigger>
                    <button
                      v-if="$page.props.jetstream.managesProfilePhotos"
                      class="flex rounded-full border-2 border-transparent text-sm transition focus:border-border focus:outline-hidden dark:focus:border-border">
                      <img
                        class="h-8 w-8 rounded-full object-cover"
                        :src="$page.props.auth.user?.profile_photo_url"
                        :alt="$page.props.auth.user?.name" />
                    </button>

                    <span v-else class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-surface px-3 py-2 text-sm font-medium leading-4 text-text-muted transition hover:text-text focus:outline-hidden dark:bg-surface dark:hover:text-text">
                        {{ $page.props.auth.user?.name }}

                        <svg
                          class="-me-0.5 ms-2 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor">
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <!-- Account Management -->
                    <div class="block px-4 py-2 text-xs text-text-muted dark:text-text">
                      {{ $t('Manage Account') }}
                    </div>

                    <!-- <JetDropdownLink :href="route('account.show')">
                                            {{ $t('Profile') }}
                                        </JetDropdownLink>

                                        <JetDropdownLink :href="route('profile.show')">
                                            {{ $t('Settings') }}
                                        </JetDropdownLink>

                                        <JetDropdownLink v-if="$page.props.auth.user?.instance_administrator" :href="route('administration.index')">
                                          Administration
                                        </JetDropdownLink>

                                        <JetDropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                            {{ $t('API Tokens') }}
                                        </JetDropdownLink> -->

                    <div class="border-t border-border dark:border-border" />

                    <!-- Authentication -->
                    <form @submit.prevent="logout">
                      <JetDropdownLink as="button">
                        {{ $t('Log Out') }}
                      </JetDropdownLink>
                    </form>
                  </template>
                </JetDropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
              <button
                class="inline-flex items-center justify-center rounded-md p-2 text-text-muted transition hover:bg-hover hover:text-text focus:bg-bg focus:text-text-muted focus:outline-hidden dark:text-text dark:hover:bg-hover dark:focus:bg-surface"
                @click="showingNavigationDropdown = !showingNavigationDropdown">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path
                    :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
                  <path
                    :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
          <div class="space-y-1 pb-3 pt-2">
            <JetResponsiveNavLink :href="route('vault.index')" :active="route().current('vault.index')">
              {{ $t('Home') }}
            </JetResponsiveNavLink>
          </div>

          <!-- Responsive Settings Options -->
          <div class="border-t border-border pb-1 pt-4 dark:border-border">
            <div class="flex items-center px-4">
              <div v-if="$page.props.jetstream.managesProfilePhotos" class="me-3 shrink-0">
                <img
                  class="h-10 w-10 rounded-full object-cover"
                  :src="$page.props.auth.user?.profile_photo_url"
                  :alt="$page.props.auth.user?.name" />
              </div>

              <div>
                <div class="text-base font-medium text-text dark:text-text">
                  {{ $page.props.auth.user?.name }}
                </div>
                <div class="text-sm font-medium text-text-muted">
                  {{ $page.props.auth.user?.email }}
                </div>
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <!-- <JetResponsiveNavLink :href="route('account.show')" :active="route().current('account.show')">
                                {{ $t('Profile') }}
                            </JetResponsiveNavLink>

                            <JetResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                {{ $t('Settings') }}
                            </JetResponsiveNavLink>

                            <JetResponsiveNavLink v-if="$page.props.auth.user?.instance_administrator" :href="route('administration.index')" :active="route().current('administration.index')">
                              Administration
                            </JetResponsiveNavLink>

                            <JetResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                {{ $t('API Tokens') }}
                            </JetResponsiveNavLink> -->

              <!-- Authentication -->
              <form method="POST" @submit.prevent="logout">
                <JetResponsiveNavLink as="button">
                  {{ $t('Log Out') }}
                </JetResponsiveNavLink>
              </form>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header v-if="$slots.header" class="bg-surface shadow-xs dark:bg-surface dark:shadow-gray-700">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="grow">
        <slot />
      </main>

      <FooterLayout />
    </div>
  </div>
</template>
