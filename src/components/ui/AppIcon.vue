<script setup>
/**
 * AppIcon — ikon SVG terpusat (Lucide, MIT). Dipakai menggantikan emoji/ikon teks.
 * Pemakaian: <AppIcon name="newspaper" :size="20" />
 */
import {
  LayoutDashboard, Settings, School, Users, Newspaper, MessageSquare, BookOpen,
  Landmark, Drama, Images, Globe, LogOut, MapPin, Phone, Mail, CalendarDays,
  Trophy, Target, ClipboardList, Check, X, ImageOff, Upload, ArrowLeft, ArrowRight,
  Hand, Sparkles, GraduationCap, Building2, Award, Star, Palette, FlaskConical,
  Calculator, Languages, Microscope, Quote, Megaphone, Bell, Utensils, Wrench,
  Music, HeartPulse, ShieldCheck, Search, Menu, ChevronRight, Info, CircleAlert,
  Plus, ListChecks, Eye, ExternalLink,
} from 'lucide-vue-next'

const props = defineProps({
  name: { type: String, required: true },
  size: { type: [Number, String], default: 20 },
  stroke: { type: [Number, String], default: 1.8 },
})

const ICONS = {
  dashboard: LayoutDashboard,
  settings: Settings,
  school: School,
  users: Users,
  newspaper: Newspaper,
  chat: MessageSquare,
  book: BookOpen,
  landmark: Landmark,
  drama: Drama,
  images: Images,
  globe: Globe,
  logout: LogOut,
  pin: MapPin,
  phone: Phone,
  mail: Mail,
  calendar: CalendarDays,
  trophy: Trophy,
  target: Target,
  clipboard: ClipboardList,
  check: Check,
  close: X,
  image: ImageOff,
  upload: Upload,
  arrowLeft: ArrowLeft,
  arrowRight: ArrowRight,
  hand: Hand,
  sparkles: Sparkles,
  grad: GraduationCap,
  building: Building2,
  award: Award,
  star: Star,
  palette: Palette,
  flask: FlaskConical,
  calculator: Calculator,
  languages: Languages,
  microscope: Microscope,
  quote: Quote,
  megaphone: Megaphone,
  bell: Bell,
  utensils: Utensils,
  wrench: Wrench,
  music: Music,
  heart: HeartPulse,
  shield: ShieldCheck,
  search: Search,
  menu: Menu,
  chevronRight: ChevronRight,
  info: Info,
  alert: CircleAlert,
  plus: Plus,
  listChecks: ListChecks,
  eye: Eye,
  external: ExternalLink,
}

const Comp = ICONS[props.name] || Sparkles
</script>

<template>
  <component :is="Comp" :size="Number(size)" :stroke-width="Number(stroke)" aria-hidden="true" />
</template>
