<script setup>
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  modelValue: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: '',
  },
  options: {
    type: Array,
    default: undefined,
  },
  textarea: {
    type: Boolean,
    default: false,
  },
  rows: {
    type: Number,
    default: 4,
  },
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <label :for="id" class="block">
    <span class="mb-2 block text-sm font-bold text-slate-700">{{ label }}</span>

    <select
      v-if="props.options"
      :id="id"
      :value="modelValue"
      class="w-full rounded-md border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
      @change="emit('update:modelValue', $event.target.value)"
    >
      <option value="" disabled>Select {{ label.toLowerCase() }}</option>
      <option v-for="option in props.options" :key="option.value" :value="option.value">
        {{ option.label }}
      </option>
    </select>

    <textarea
      v-else-if="textarea"
      :id="id"
      :rows="rows"
      :value="modelValue"
      :placeholder="placeholder"
      class="w-full resize-none rounded-md border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
      @input="emit('update:modelValue', $event.target.value)"
    ></textarea>

    <input
      v-else
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      class="w-full rounded-md border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
      @input="emit('update:modelValue', $event.target.value)"
    />
  </label>
</template>
