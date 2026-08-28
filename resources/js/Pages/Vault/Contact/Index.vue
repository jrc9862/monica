<script setup>
import { computed } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import Layout from '@/Layouts/Layout.vue';
import CrmAvatar from '@/Shared/CrmAvatar.vue';
import CrmIcon from '@/Shared/Icons/CrmIcon.vue';
import PanelHeader from '@/Shared/Crm/PanelHeader.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  layoutData: Object,
  data: Object,
  paginator: Object,
});

const form = useForm({
  sort_order: props.data.user_contact_sort_order,
});

const update = () => {
  axios.put(props.data.url.sort.update, form).then((response) => {
    localStorage.success = trans('Changes saved');
    router.visit(response.data.data);
  });
};

// The filter reads as a command-line flag: --sort=last-updated.
const sortFlag = computed(() => String(form.sort_order).replace(/_/g, '-'));
const filterFlag = computed(() => (props.data.current_label_name ?? '').toLowerCase().replace(/ /g, '-'));
</script>

<template>
  <Layout :title="$t('Contacts')" :layout-data="layoutData" :inside-vault="true">
    <div class="flex min-w-0 flex-1">
      <!-- labels -->
      <div class="w-[230px] flex-none py-6 ps-6">
        <PanelHeader icon="tag" :title="$t('Labels')" />

        <div class="flex flex-col gap-2">
          <Link
            :href="data.url.contact.index"
            class="crm-label-row -mx-1.5 flex items-center gap-2 rounded-[2px] px-1.5 py-0.5 text-left"
            :style="{ background: data.current_label ? 'transparent' : 'var(--primary-soft)' }">
            <span class="h-2 w-2 flex-none rounded-[2px]" style="background: var(--muted)" />
            <span style="font: 400 13px var(--mono); color: var(--ink)">{{ $t('View all') }}</span>
            <span style="font: 400 11px var(--mono); color: var(--muted)">{{ data.contacts_total }}</span>
          </Link>

          <Link
            v-for="label in data.labels"
            :key="label.id"
            :href="label.url.show"
            class="crm-label-row -mx-1.5 flex items-center gap-2 rounded-[2px] px-1.5 py-0.5 text-left"
            :style="{ background: label.id === data.current_label ? 'var(--primary-soft)' : 'transparent' }">
            <span class="h-2 w-2 flex-none rounded-[2px]" :class="label.bg_color" />
            <span style="font: 400 13px var(--mono); color: var(--primary)">{{ label.name }}</span>
            <span style="font: 400 11px var(--mono); color: var(--muted)">{{ label.count }}</span>
          </Link>

          <p v-if="data.labels.length === 0" class="crm-meta">{{ $t('No labels yet.') }}</p>
        </div>
      </div>

      <!-- table -->
      <div class="min-w-0 flex-1 p-6">
        <div class="mb-5 flex items-center justify-between">
          <div class="flex min-w-0 items-center gap-[14px]">
            <span class="crm-section-title whitespace-nowrap">
              {{ $t('Contacts in this vault') }}
              <span style="font: 400 20px var(--mono); color: var(--muted)">· {{ data.contacts.length }}</span>
            </span>

            <Link
              v-if="data.current_label"
              :href="data.url.contact.index"
              :title="$t('Clear filter')"
              class="crm-filter-chip flex h-[30px] items-center gap-2 whitespace-nowrap rounded-[2px] px-2.5"
              style="
                border: 1px solid var(--primary);
                background: var(--primary-soft);
                color: var(--primary);
                font: 500 11px var(--mono);
                letter-spacing: 0.08em;
                text-transform: uppercase;
              ">
              <span style="color: var(--muted)">--label=</span>{{ filterFlag }}
              <CrmIcon name="close" :size="13" />
            </Link>
          </div>

          <div class="flex gap-3">
            <label
              class="crm-sort relative flex h-11 cursor-pointer items-center gap-3 whitespace-nowrap rounded-[2px] px-5"
              style="
                border: 1px solid var(--line);
                background: var(--card);
                color: var(--ink);
                font: 500 12px var(--mono);
                letter-spacing: 0.1em;
                text-transform: uppercase;
              ">
              <span style="color: var(--muted)">--sort=</span>{{ sortFlag }}
              <CrmIcon name="arrow-down" :size="18" />
              <select
                v-model="form.sort_order"
                class="absolute inset-0 cursor-pointer appearance-none opacity-0"
                @change="update()">
                <option v-for="order in data.contact_sort_orders" :key="order.id" :value="order.id">
                  {{ order.name }}
                </option>
              </select>
            </label>

            <Link
              v-if="layoutData.vault.permission.at_least_editor"
              :href="data.url.contact.create"
              class="crm-press crm-btn h-11 px-5">
              <CrmIcon name="plus" :size="18" />
              {{ $t('Add a contact') }}
            </Link>
          </div>
        </div>

        <div class="crm-card overflow-hidden">
          <!-- column labels -->
          <div
            class="flex h-14 items-center gap-6 px-6"
            style="border-bottom: 1px solid var(--divider); background: var(--bg)">
            <span class="w-9 flex-none" />
            <span class="crm-col" style="flex: 1.4">{{ $t('Name') }}</span>
            <span class="crm-col" style="flex: 1.2">{{ $t('Labels') }}</span>
            <span class="crm-col w-[150px] flex-none">{{ $t('Last activity') }}</span>
            <span class="w-10 flex-none" />
          </div>

          <div
            v-for="contact in data.contacts"
            :key="contact.id"
            class="crm-row-hover flex h-[82px] cursor-pointer items-center gap-6 px-6"
            style="border-bottom: 1px solid var(--divider)"
            @click="router.visit(contact.url.show)">
            <CrmAvatar :data="contact.avatar" :size="46" />

            <div class="min-w-0" style="flex: 1.4">
              <div class="crm-name overflow-hidden text-ellipsis whitespace-nowrap">{{ contact.name }}</div>
              <div v-if="contact.job" style="font: 400 12px var(--mono); color: var(--muted)">{{ contact.job }}</div>
            </div>

            <!-- label chips filter the list; they must not open the contact -->
            <div class="flex min-w-0 flex-wrap gap-1.5" style="flex: 1.2">
              <Link
                v-for="label in contact.labels"
                :key="label.id"
                :href="label.url.show"
                :title="$t('Filter by this label')"
                class="crm-chip"
                @click.stop>
                {{ label.name }}
              </Link>
            </div>

            <span
              class="w-[150px] flex-none whitespace-nowrap"
              style="
                font:
                  400 13px Inter,
                  sans-serif;
                color: var(--muted);
              ">
              {{ contact.last_activity }}
            </span>

            <span class="flex w-10 flex-none justify-end" style="color: var(--faint)">
              <CrmIcon name="arrow-right" :size="18" />
            </span>
          </div>

          <div v-if="data.contacts.length === 0" class="flex flex-col items-center gap-3 px-6 py-12">
            <span class="flex" style="color: var(--faint)"><CrmIcon name="tag" :size="28" /></span>
            <span class="crm-meta-12">{{ $t('No contacts carry this label.') }}</span>
          </div>
        </div>

        <div class="mt-6">
          <Pagination :items="paginator" />
        </div>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
.crm-col {
  font: 500 11px var(--mono);
  color: var(--muted);
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.crm-label-row:hover {
  opacity: 0.7;
  text-decoration: none;
}

.crm-filter-chip:hover {
  border-color: var(--ink);
  color: var(--ink);
  text-decoration: none;
}

.crm-sort:hover {
  border-color: var(--primary);
}
</style>
