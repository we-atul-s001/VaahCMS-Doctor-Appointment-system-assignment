<script setup>
import { ref, defineProps, watch } from 'vue';

defineOptions({
    inheritAttrs: false,
})

const props = defineProps({
    stacked: {
        type: Boolean,
        default: false
    },
    stackType: {
        type: String,
    },
    title: {
        type: String,
        default: 'Bar Chart'
    },
    chartOptions: {
        type: Object,
        default: () => ({}),
    },
    chartSeries: {
        type: Array,
        required: true,
    },
    titleAlign: {
        type: String,
        default: 'center'
    },
});

const chartOptions = ref({
    chart: {
        stacked: props.stacked,
        stackType: props.stackType,
    },
    plotOptions: {
        bar: {}
    },
    xaxis: {
        categories: ['Doctors', 'Patients'], 
    },
    yaxis: {
        title: {
            text: props.chartOptions.yaxisTitle,
        },
    },
    title: {
        text: props.title,
        align: props.titleAlign
    },
});

const chartSeries = ref(props.chartSeries);

watch(() => props.chartOptions, (newOptions) => {
    chartOptions.value = { ...chartOptions.value, ...newOptions };
}, { immediate: true });

watch(() => props.chartSeries, (newSeries) => {
    chartSeries.value = newSeries;
}, { immediate: true });
</script>
<template>
    <div>
        <ApexChart
            :options="chartOptions"
            :series="chartSeries"
            v-bind="$attrs"
        />
    </div>
</template>


