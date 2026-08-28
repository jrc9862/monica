<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';
import AddMenu from '@/Shared/Crm/AddMenu.vue';

const props = defineProps({
  title: { type: String, default: '' },
  layoutData: { type: Object, required: true },
  insideVault: { type: Boolean, default: false },
  // Contact detail widens the left track, so the search field right-aligns
  // beside the add button instead of sitting dead centre.
  compact: { type: Boolean, default: false },
  backUrl: { type: String, default: null },
  backLabel: { type: String, default: null },
});

const search = ref(null);
const menuOpen = ref(false);

const leftStyle = { flex: '1', minWidth: '0' };
const searchStyle = computed(() =>
  props.compact
    ? { flex: '0 1 380px', maxWidth: '380px', minWidth: '200px' }
    : { flex: '0 1 440px', maxWidth: '440px', minWidth: '220px' },
);
const rightStyle = computed(() => (props.compact ? { flex: 'none' } : { flex: '1', minWidth: '0' }));

const goToSearchPage = () => {
  router.visit(props.layoutData.vault.url.search, { data: { searchTerm: '' } });
};

// `/` focuses the search field, as the key hint chip promises.
const onKey = (e) => {
  if (e.key !== '/' || e.metaKey || e.ctrlKey) return;
  const tag = document.activeElement?.tagName;
  if (tag === 'INPUT' || tag === 'TEXTAREA' || document.activeElement?.isContentEditable) return;
  e.preventDefault();
  search.value?.focus();
};

onMounted(() => document.addEventListener('keydown', onKey));
onUnmounted(() => document.removeEventListener('keydown', onKey));
</script>

<template>
  <header
    class="sticky top-0 z-20 flex h-[90px] flex-none items-center gap-6 px-6"
    style="border-bottom: 1px solid var(--line); background: var(--bar); backdrop-filter: blur(10px)">
    <div class="flex items-center gap-4" :style="leftStyle">
      <h1
        class="m-0 flex items-center gap-[10px] whitespace-nowrap"
        style="font: 700 20px/40px var(--mono); color: var(--ink)">
        <span style="color: var(--primary)">&#9646;</span>{{ title }}
      </h1>

      <div
        class="flex h-8 min-w-0 shrink items-center gap-1.5 whitespace-nowrap rounded-[2px] px-3"
        style="border: 1px solid var(--line); background: var(--card)">
        <Link :href="layoutData.url.vaults" style="font: 500 12px var(--mono); color: var(--primary)">
          {{ layoutData.user.name }}
        </Link>
        <template v-if="layoutData.vault">
          <span style="font: 400 12px var(--mono); color: var(--faint)">/</span>
          <span class="min-w-0 overflow-hidden text-ellipsis" style="font: 400 12px var(--mono); color: var(--ink2)">
            {{ layoutData.vault.name }}
          </span>
        </template>
      </div>

      <Link v-if="backUrl" :href="backUrl" class="crm-btn-quiet">
        <CrmIcon name="arrow-left" :size="14" />
        {{ backLabel }}
      </Link>
    </div>

    <div
      v-if="insideVault"
      class="flex h-[50px] items-center gap-[10px] rounded-[2px] px-[18px]"
      :style="searchStyle"
      style="border: 1px solid var(--line); background: var(--card)">
      <span class="flex flex-none" style="color: var(--muted)">
        <CrmIcon name="search" :size="18" />
      </span>
      <input
        ref="search"
        type="text"
        :placeholder="$t('Search this vault')"
        class="min-w-0 flex-1 border-none bg-transparent outline-none"
        style="font: 400 13px var(--mono); color: var(--ink)"
        @focus="goToSearchPage" />
      <span
        class="flex-none px-[7px] py-[2px]"
        style="border: 1px solid var(--line); background: var(--bg); font: 500 11px var(--mono); color: var(--muted)">
        /
      </span>
    </div>

    <div class="flex items-center justify-end gap-4" :style="rightStyle">
      <button
        v-if="insideVault"
        type="button"
        :title="$t('Add')"
        class="crm-press flex h-[50px] w-[50px] flex-none cursor-pointer items-center justify-center"
        style="background: var(--primary); color: var(--on-primary)"
        @click="menuOpen = true">
        <CrmIcon name="plus" :size="22" />
      </button>

      <Link :href="layoutData.url.settings" class="flex-none">
        <span class="crm-tint h-[50px] w-[50px]" style="background: var(--primary-soft); color: var(--primary)">
          <CrmIcon name="face" :size="26" />
        </span>
      </Link>
    </div>
  </header>

  <AddMenu v-if="menuOpen" :layout-data="layoutData" @close="menuOpen = false" />
</template>
