<script setup>
import { computed } from 'vue';
import {
  ArrowDown,
  ArrowLeft,
  ArrowRight,
  Bell,
  Building2,
  Cake,
  Calendar,
  ChartColumn,
  Check,
  CircleCheck,
  Clock,
  FileText,
  Flag,
  Folder,
  Gift,
  Heart,
  House,
  Image,
  LayoutGrid,
  ListChecks,
  LogOut,
  MapPin,
  MoreHorizontal,
  Moon,
  Network,
  NotebookPen,
  Paperclip,
  Phone,
  Plus,
  Search,
  Settings,
  Smile,
  Star,
  Sun,
  Tag,
  UserCheck,
  Users,
  X,
} from 'lucide-vue-next';

const props = defineProps({
  name: { type: String, required: true },
  size: { type: [Number, String], default: 20 },
});

// The design names its glyphs; the repo draws them with lucide. Contacts and
// Groups deliberately do not share a glyph.
const glyphs = {
  'arrow-down': ArrowDown,
  'arrow-left': ArrowLeft,
  'arrow-right': ArrowRight,
  bell: Bell,
  buildings: Building2,
  cake: Cake,
  calendar: Calendar,
  call: Phone,
  chart: ChartColumn,
  check: Check,
  clip: Paperclip,
  clock: Clock,
  close: X,
  document: FileText,
  door: LogOut,
  face: Smile,
  flag: Flag,
  folder: Folder,
  gift: Gift,
  grid: LayoutGrid,
  group: Network,
  heart: Heart,
  home: House,
  location: MapPin,
  moon: Moon,
  more: MoreHorizontal,
  note: NotebookPen,
  people: Users,
  photo: Image,
  plus: Plus,
  search: Search,
  setting: Settings,
  star: Star,
  sun: Sun,
  tag: Tag,
  task: ListChecks,
  'tick-circle': CircleCheck,
  'user-check': UserCheck,
};

const glyph = computed(() => glyphs[props.name] ?? LayoutGrid);
const px = computed(() => `${props.size}px`);
</script>

<template>
  <component :is="glyph" :size="Number(size)" :style="{ width: px, height: px, flex: 'none' }" :stroke-width="2" />
</template>
