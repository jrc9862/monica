<template>
  <div class="crm-grid">
    <slot />
  </div>
</template>

<style scoped>
.crm-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}
</style>
