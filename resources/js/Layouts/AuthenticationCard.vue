<template>
  <div class="flex min-h-screen flex-col items-center bg-bg pt-6 dark:bg-surface sm:justify-center sm:pt-0">
    <div>
      <slot name="logo" />
    </div>

    <div
      class="mt-6 w-full overflow-hidden bg-surface px-6 py-4 shadow-md dark:bg-surface dark:shadow-gray-700 sm:max-w-md sm:rounded-lg">
      <slot />
    </div>
  </div>
</template>
