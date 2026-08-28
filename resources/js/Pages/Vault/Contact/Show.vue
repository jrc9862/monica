<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { flash } from '@/methods.js';
import JetDialogModal from '@/Components/Jetstream/DialogModal.vue';
import JetConfirmationModal from '@/Components/Jetstream/ConfirmationModal.vue';
import JetButton from '@/Components/Button.vue';
import JetDangerButton from '@/Components/Jetstream/DangerButton.vue';
import JetSecondaryButton from '@/Components/Jetstream/SecondaryButton.vue';
import Layout from '@/Layouts/Layout.vue';
import ContactName from '@/Shared/Modules/ContactName.vue';
import ContactAvatar from '@/Shared/Modules/ContactAvatar.vue';
import GenderPronoun from '@/Shared/Modules/GenderPronoun.vue';
import FamilySummary from '@/Shared/Modules/FamilySummary.vue';
import Notes from '@/Shared/Modules/Notes.vue';
import ImportantDates from '@/Shared/Modules/ImportantDates.vue';
import Labels from '@/Shared/Modules/Labels.vue';
import Reminders from '@/Shared/Modules/Reminders.vue';
import Feed from '@/Shared/Modules/Feed.vue';
import Loans from '@/Shared/Modules/Loans.vue';
import JobInformation from '@/Shared/Modules/JobInformation.vue';
import Relationships from '@/Shared/Modules/Relationships.vue';
import Tasks from '@/Shared/Modules/Tasks.vue';
import Calls from '@/Shared/Modules/Calls.vue';
import Pets from '@/Shared/Modules/Pets.vue';
import Goals from '@/Shared/Modules/Goals.vue';
import Addresses from '@/Shared/Modules/Addresses.vue';
import Groups from '@/Shared/Modules/Groups.vue';
import ContactInformation from '@/Shared/Modules/ContactInformation.vue';
import Documents from '@/Shared/Modules/Documents.vue';
import Photos from '@/Shared/Modules/Photos.vue';
import Religion from '@/Shared/Modules/Religion.vue';
import Posts from '@/Shared/Modules/Posts.vue';
import LifeEvent from '@/Shared/Modules/LifeEvent.vue';
import QuickFacts from '@/Shared/Modules/QuickFacts.vue';
import Uploadcare from '@/Components/Uploadcare.vue';

const props = defineProps({
  layoutData: Object,
  data: Object,
});

const form = useForm({
  searchTerm: null,
  uuid: null,
  name: null,
  original_url: null,
  cdn_url: null,
  mime_type: null,
  size: null,
  errors: [],
});

const deletingContact = ref(false);
const deleteContactForm = reactive({
  processing: false,
});

const togglingArchive = ref(false);
const toggleArchiveForm = reactive({
  processing: false,
});

const destroy = () => {
  deleteContactForm.processing = true;

  axios
    .delete(props.data.url.destroy)
    .then((response) => {
      deleteContactForm.processing = false;

      localStorage.success = trans('The contact has been deleted');
      router.visit(response.data.data);
    })
    .catch((error) => {
      deleteContactForm.processing = false;
      form.errors = error.response.data;
    });
};

const toggleArchive = () => {
  toggleArchiveForm.processing = true;

  axios
    .put(props.data.url.toggle_archive)
    .then((response) => {
      toggleArchiveForm.processing = false;

      localStorage.success = trans('Changes saved');
      router.visit(response.data.data);
    })
    .catch((error) => {
      toggleArchiveForm.processing = false;
      form.errors = error.response.data;
    });
};

const onSuccess = (file) => {
  form.uuid = file.uuid;
  form.name = file.name;
  form.original_url = file.originalUrl;
  form.cdn_url = file.cdnUrl;
  form.mime_type = file.mimeType;
  form.size = file.size;

  upload();
};

const upload = () => {
  axios
    .put(props.data.url.update_avatar, form)
    .then((response) => {
      router.visit(response.data.data);
      flash(trans('The photo has been added'), 'success');
    })
    .catch((error) => {
      form.errors = error.response.data;
    });
};

const destroyAvatar = () => {
  axios
    .delete(props.data.url.destroy_avatar)
    .then((response) => {
      router.visit(response.data.data);
      flash(trans('Changes saved'), 'success');
    })
    .catch((error) => {
      form.errors = error.response.data;
    });
};

const download = () => {
  router.post(props.data.url.download_vcard, null, {
    preserveScroll: true,
    onSuccess: (response) => {
      const filename = response.props.jetstream.flash.filename;
      if (filename !== undefined) {
        const url = window.URL.createObjectURL(new Blob([response.props.jetstream.flash.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename);
        try {
          document.body.appendChild(link);
          link.click();
        } catch {
          document.body.removeChild(link);
        }
      }
    },
  });
};

const selectedOption = ref('');

onMounted(() => {
  const selectedPage = Object.values(props.data.template_pages).find((page) => page.selected);
  if (selectedPage) {
    selectedOption.value = selectedPage.url.show;
  }
});

const navigateToSelected = () => {
  router.visit(selectedOption.value);
};
</script>

<template>
  <Layout
    :title="data.contact_name.name"
    :layout-data="layoutData"
    :inside-vault="true"
    :compact-header="true"
    :back-url="layoutData.vault.url.contacts"
    :back-label="$t('All contacts')">
    <div class="flex min-w-0 flex-1">
      <!-- left column -->
      <div class="w-[300px] flex-none p-6" style="border-right: 1px solid var(--line)">
        <div v-if="data.contact_information.length > 0" class="mb-8">
          <div v-for="module in data.contact_information" :key="module.id">
            <ContactAvatar v-if="module.type === 'avatar'" :data="module.data" />

            <ContactName v-else-if="module.type === 'contact_names'" :data="module.data" />

            <FamilySummary v-else-if="module.type === 'family_summary'" :data="module.data" />

            <GenderPronoun v-else-if="module.type === 'gender_pronoun'" :data="module.data" />

            <ImportantDates v-else-if="module.type === 'important_dates'" :data="module.data" />

            <Labels v-else-if="module.type === 'labels'" :data="module.data" />

            <JobInformation v-else-if="module.type === 'company'" :data="module.data" />

            <Religion v-else-if="module.type === 'religions'" :data="module.data" />
          </div>
        </div>

        <ul class="crm-actions mt-6 flex flex-col gap-2">
          <!-- remove avatar -->
          <li v-if="data.avatar.hasFile" class="mb-2">
            <span @click.prevent="destroyAvatar()" class="cursor-pointer text-accent hover:underline">
              {{ $t('Remove avatar') }}
            </span>
          </li>
          <!-- upload new avatar -->
          <li v-if="!data.avatar.hasFile" class="mb-2">
            <Uploadcare
              v-if="data.avatar.uploadcare.publicKey && data.avatar.canUploadFile"
              :public-key="data.avatar.uploadcare.publicKey"
              :secure-signature="data.avatar.uploadcare.signature"
              :secure-expire="data.avatar.uploadcare.expire"
              :tabs="'file'"
              :preview-step="false"
              @success="onSuccess"
              @error="onError">
              <span class="cursor-pointer text-accent hover:underline"> {{ $t('Upload photo as avatar') }} </span>
            </Uploadcare>
          </li>
          <!-- archive contact -->
          <li v-if="data.listed && data.options.can_be_archived" class="mb-2">
            <span class="cursor-pointer text-accent hover:underline" @click="togglingArchive = true">
              {{ $t('Archive contact') }}
            </span>
          </li>
          <!-- unarchive contact -->
          <li v-if="!data.listed" class="mb-2">
            <span class="cursor-pointer text-accent hover:underline" @click="togglingArchive = true">
              {{ $t('Unarchive contact') }}
            </span>
          </li>
          <!-- change template -->
          <li class="mb-2">
            <Link :href="data.url.update_template" class="cursor-pointer text-accent hover:underline">
              {{ $t('Change template') }}
            </Link>
          </li>
          <!-- move contact to another vault -->
          <li class="mb-2">
            <Link :href="data.url.move_contact" class="cursor-pointer text-accent hover:underline">
              {{ $t('Move contact') }}
            </Link>
          </li>
          <!-- download as vcard -->
          <li class="mb-2">
            <Link @click.prevent="download()" class="cursor-pointer text-accent hover:underline">
              {{ $t('Download as vCard') }}
            </Link>
          </li>
          <!-- delete contact -->
          <li v-if="data.options.can_be_deleted">
            <span class="cursor-pointer text-accent hover:underline" @click="deletingContact = true">
              {{ $t('Delete contact') }}
            </span>
          </li>
        </ul>
      </div>

      <!-- right column -->
      <div class="min-w-0 flex-1 p-6">
        <div v-if="!data.listed" class="crm-card mb-6 px-4 py-2.5 text-center" style="border-color: var(--yellow)">
          <span class="crm-meta-12">{{ $t('The contact is archived') }}</span>
        </div>

        <!-- quick facts -->
        <QuickFacts
          v-if="data.quick_fact_template_entries.templates.length > 0"
          :data="data.quick_fact_template_entries" />

        <!-- family summary -->
        <div v-if="data.group_summary_information.length > 0">
          <div class="mb-6 flex rounded-xs border border-border p-3 dark:border-border">
            <img src="/img/group.svg" class="me-2 h-6 w-6" />
            <ul>
              <li class="me-2 inline">{{ $t('Part of') }}</li>
              <li v-for="group in data.group_summary_information" :key="group.id" class="group-list-item me-2 inline">
                <Link :href="group.url.show" class="text-accent hover:underline">
                  {{ group.name }}
                </Link>
              </li>
            </ul>
          </div>
        </div>

        <!-- page selector on desktop -->
        <div class="mb-8 hidden w-full md:block" style="border-bottom: 1px solid var(--line)">
          <div class="flex overflow-x-hidden">
            <Link
              v-for="page in data.template_pages"
              :key="page.id"
              :href="page.url.show"
              class="crm-tab me-2 flex-none px-3 pb-2.5 pt-1"
              :style="{
                color: page.selected ? 'var(--ink)' : 'var(--muted)',
                borderBottom: `2px solid ${page.selected ? 'var(--primary)' : 'transparent'}`,
              }">
              {{ page.name }}
            </Link>
          </div>
        </div>

        <!-- page selector on mobile -->
        <div class="md:hidden mb-8 w-full">
          <p class="text-sm mb-2">{{ $t('Select a page') }}</p>
          <select
            v-model="selectedOption"
            @change="navigateToSelected"
            class="w-full rounded-md border-border py-2 pl-3 pr-10 text-base focus:border-accent focus:outline-hidden focus:ring-accent dark:border-border dark:bg-surface dark:text-text">
            <option v-for="page in data.template_pages" :key="page.id" :value="page.url.show">
              {{ page.name }}
            </option>
          </select>
        </div>

        <!-- all the modules -->
        <div v-if="data.modules.length > 0">
          <div v-for="module in data.modules" :key="module.id">
            <Notes v-if="module.type === 'notes'" :data="module.data" />

            <Reminders v-else-if="module.type === 'reminders'" :data="module.data" />

            <Feed v-else-if="module.type === 'feed'" :url="module.data" />

            <Loans v-else-if="module.type === 'loans'" :data="module.data" :layout-data="layoutData" />

            <Relationships v-else-if="module.type === 'relationships'" :data="module.data" />

            <Tasks v-else-if="module.type === 'tasks'" :data="module.data" />

            <Calls v-else-if="module.type === 'calls'" :data="module.data" />

            <Pets v-else-if="module.type === 'pets'" :data="module.data" />

            <Goals v-else-if="module.type === 'goals'" :data="module.data" />

            <Addresses v-else-if="module.type === 'addresses'" :data="module.data" />

            <Groups v-else-if="module.type === 'groups'" :data="module.data" />

            <ContactInformation v-else-if="module.type === 'contact_information'" :data="module.data" />

            <Documents v-else-if="module.type === 'documents'" :data="module.data" />

            <Photos v-else-if="module.type === 'photos'" :data="module.data" />

            <Posts v-else-if="module.type === 'posts'" :data="module.data" />

            <Life-Event v-else-if="module.type === 'life_events'" :data="module.data" :layout-data="layoutData" />
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Contact Confirmation Modal -->
    <JetConfirmationModal :show="deletingContact" @close="deletingContact = false">
      <template #title>
        {{ $t('Delete contact') }}
      </template>

      <template #content>
        {{
          $t(
            'Are you sure you would like to delete this contact? This will remove everything we know about this contact.',
          )
        }}
      </template>

      <template #footer>
        <JetSecondaryButton @click="deletingContact = false">
          {{ $t('Cancel') }}
        </JetSecondaryButton>

        <JetDangerButton
          class="ms-3"
          :class="{ 'opacity-25': deleteContactForm.processing }"
          :disabled="deleteContactForm.processing"
          @click="destroy">
          {{ $t('Delete') }}
        </JetDangerButton>
      </template>
    </JetConfirmationModal>

    <!-- Archive Contact Confirmation Modal -->
    <JetDialogModal :show="togglingArchive" @close="togglingArchive = false">
      <template #title>
        {{ $t('Archive contact') }}
      </template>

      <template #content>
        {{ $t('Are you sure you would like to archive this contact?') }}
      </template>

      <template #footer>
        <JetSecondaryButton @click="togglingArchive = false">
          {{ $t('Cancel') }}
        </JetSecondaryButton>

        <JetButton
          class="ms-3"
          :class="{ 'opacity-25': toggleArchiveForm.processing }"
          :disabled="toggleArchiveForm.processing"
          @click="toggleArchive">
          {{ $t('Archive') }}
        </JetButton>
      </template>
    </JetDialogModal>
  </Layout>
</template>

<style lang="scss" scoped>
.group-list-item:not(:last-child):after {
  content: ',';
}

.crm-tab {
  font:
    500 14px Inter,
    sans-serif;
  text-decoration: none;

  &:hover {
    color: var(--ink);
    text-decoration: none;
  }
}

/* A column of small mono text actions, ending in a delete in --pink. */
.crm-actions {
  font: 500 12px var(--mono);
  letter-spacing: 0.04em;

  span,
  a {
    color: var(--primary);
    cursor: pointer;
  }

  li:last-child span {
    color: var(--pink);
  }
}
</style>
