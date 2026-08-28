<script setup>
import { Link } from '@inertiajs/vue3';
import Layout from '@/Layouts/Layout.vue';
import CrmCardGrid from '@/Shared/Crm/CrmCardGrid.vue';
import CrmAvatar from '@/Shared/CrmAvatar.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';
import { Settings } from 'lucide-vue-next';
import { trans } from 'laravel-vue-i18n';

defineProps({
  layoutData: Object,
  data: Object,
});

// The tint rotates through the design's three accents so a wall of vaults
// still reads as distinct cards.
const tints = [
  { bg: 'var(--blush)', fg: 'var(--pink)', icon: 'heart' },
  { bg: 'var(--primary-soft)', fg: 'var(--primary)', icon: 'buildings' },
  { bg: 'var(--sun)', fg: 'var(--yellow)', icon: 'note' },
];
const tint = (index) => tints[index % tints.length];

const meta = (vault) => {
  const total = vault.contacts.length + vault.remaining_contacts;
  return trans(':count contacts', { count: total });
};
</script>

<template>
  <Layout :title="$t('Your vaults')" :layout-data="layoutData">
    <div class="min-w-0 flex-1 p-6">
      <!-- blank state -->
      <div v-if="data.vaults.length === 0" class="mx-auto max-w-xl">
        <div class="crm-card p-6">
          <h2
            class="mb-4"
            style="
              font:
                700 22px Inter,
                sans-serif;
              color: var(--ink);
            ">
            {{ $t('Thanks for giving Monica a try.') }}
          </h2>
          <p class="crm-body mb-3">
            {{ $t('Monica was made to help you document your life and your social interactions.') }}
          </p>
          <p class="crm-body mb-6">
            {{
              $t('To start, you need to create a vault. A vault is a private space where you can store your contacts.')
            }}
          </p>
          <Link :href="data.url.vault.create" class="crm-press crm-btn">
            <CrmIcon name="plus" :size="18" />
            {{ $t('Create a vault') }}
          </Link>
        </div>
      </div>

      <CrmCardGrid v-else>
        <div v-for="(vault, index) in data.vaults" :key="vault.id" class="crm-card crm-card-hover flex flex-col">
          <Link :href="vault.url.show" class="flex flex-1 flex-col gap-3.5 p-6 no-underline">
            <span class="crm-tint h-[52px] w-[52px]" :style="{ background: tint(index).bg, color: tint(index).fg }">
              <CrmIcon :name="tint(index).icon" :size="24" />
            </span>

            <span
              style="
                font:
                  700 22px Inter,
                  sans-serif;
                color: var(--ink);
              "
              >{{ vault.name }}</span
            >

            <span
              v-if="vault.description"
              style="
                font:
                  400 15px/26px Inter,
                  sans-serif;
                color: var(--ink2);
              ">
              {{ vault.description }}
            </span>
            <span v-else class="crm-meta-12">{{ $t('No description yet.') }}</span>

            <span class="crm-meta-12 mt-auto">{{ meta(vault) }}</span>
          </Link>

          <div class="flex items-center justify-between px-6 py-3" style="border-top: 1px solid var(--divider)">
            <div class="flex">
              <span v-for="contact in vault.contacts" :key="contact.id" class="-ms-2 first:ms-0">
                <CrmAvatar :data="contact.avatar" :size="24" />
              </span>
              <span v-if="vault.remaining_contacts !== 0" class="crm-meta ms-2 self-center">
                + {{ vault.remaining_contacts }}
              </span>
            </div>

            <Link :href="vault.url.settings" :title="$t('Settings')" style="color: var(--muted)">
              <Settings class="h-[18px] w-[18px]" />
            </Link>
          </div>
        </div>

        <Link
          :href="data.url.vault.create"
          class="crm-create-tile flex min-h-[200px] cursor-pointer flex-col items-center justify-center gap-2.5 rounded-[2px] no-underline"
          style="border: 1px dashed var(--faint); color: var(--muted)">
          <CrmIcon name="plus" :size="28" />
          <span style="font: 500 12px var(--mono); letter-spacing: 0.1em; text-transform: uppercase">
            {{ $t('Create a vault') }}
          </span>
        </Link>
      </CrmCardGrid>
    </div>
  </Layout>
</template>

<style scoped>
.crm-create-tile {
  transition:
    transform 0.15s cubic-bezier(0.4, 0, 0.2, 1),
    box-shadow 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.crm-create-tile:hover {
  border-color: var(--primary);
  color: var(--primary);
  box-shadow: 4px 4px 0 var(--stamp);
  transform: translate(-1px, -1px);
}
</style>
