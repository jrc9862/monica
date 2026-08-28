<template>
  <span :class="'relative cursor-pointer border border-border bg-surface-raised text-sm text-text hover:border-accent'">
    {{ text }}
  </span>
</template>

<script>
export default {
  props: {
    text: {
      type: String,
      default: '',
    },
  },
};
</script>

<style lang="scss" scoped>
span {
  --tw-shadow: 4px 4px 0 var(--stamp) !important;
  border-radius: 2px !important;
  border-width: 1px !important;
  border-color: var(--stamp) !important;
  box-shadow:
    var(--tw-ring-offset-shadow, 0 0 transparent), var(--tw-ring-shadow, 0 0 transparent), var(--tw-shadow) !important;
  display: inline-block !important;
  position: relative !important;
  text-decoration: none !important;
  transition-duration: 0.15s !important;
  transition-property:
    background-color,
    border-color,
    color,
    fill,
    stroke,
    opacity,
    box-shadow,
    transform,
    filter,
    backdrop-filter,
    -webkit-backdrop-filter !important;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1) !important;
  font: 500 12px var(--mono) !important;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  line-height: 44px !important;
  padding-left: 18px;
  padding-right: 18px;

  &:hover {
    box-shadow: none !important;
    transform: translate(4px, 4px);
  }
}
</style>
