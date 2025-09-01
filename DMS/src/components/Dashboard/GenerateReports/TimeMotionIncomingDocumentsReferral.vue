<script setup lang="ts">
import { ref, computed } from 'vue'
import { ClockIcon } from '@heroicons/vue/24/outline'

// Sample data based on the image structure
const allReportData = ref([
  {
    id: 1, month: 'January', routingOffice: 'Records Unit', jan: '1,843:39', feb: '979:14', mar: '1,753:01', apr: '1,154:02', may: '2,117:31', jun: '1,065:57', avgHours1stSem: 2, jul: '903:03', aug: '213:49', sep: '-', oct: '-', nov: '-', dec: '1:55', avgHours2ndSem: 1
  },
  {
    id: 2, month: 'February', routingOffice: 'ORED', jan: '4,409:21', feb: '4,050:15', mar: '4,045:28', apr: '2,548:05', may: '3,025:06', jun: '3,683:06', avgHours1stSem: 14, jul: '3,455:26', aug: '732:28', sep: '-', oct: '-', nov: '-', dec: '1:44', avgHours2ndSem: 8
  },
  {
    id: 3, month: 'March', routingOffice: 'OARD MS', jan: '736:58', feb: '696:41', mar: '995:33', apr: '817:21', may: '1,215:12', jun: '1,008:51', avgHours1stSem: 3, jul: '1,328:06', aug: '224:14', sep: '-', oct: '-', nov: '-', dec: '0:00', avgHours2ndSem: 2
  },
  {
    id: 4, month: 'April', routingOffice: 'OARD TS', jan: '5,317:07', feb: '7,463:54', mar: '13,945:49', apr: '8,263:46', may: '6,585:20', jun: '8,756:57', avgHours1stSem: 11, jul: '11,635:32', aug: '1,778:35', sep: '-', oct: '-', nov: '-', dec: '6:25', avgHours2ndSem: 8
  },
  {
    id: 5, month: 'May', routingOffice: 'RSCIS', jan: '184:46', feb: '122:33', mar: '208:41', apr: '230:23', may: '112:02', jun: '152:16', avgHours1stSem: 5, jul: '1,132:44', aug: '218:57', sep: '-', oct: '-', nov: '-', dec: '0:00', avgHours2ndSem: 16
  },
])

// Reactive state for month filtering
const selectedMonthRange = ref('all')

// Filtered report data based on selectedMonthRange
const filteredReportData = computed(() => {
  const currentMonthName = new Date().toLocaleString('default', { month: 'long' });
  const allMonths = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  const currentMonthIndex = allMonths.indexOf(currentMonthName);

  switch (selectedMonthRange.value) {
    case 'current-month':
      return allReportData.value.filter(item => item.month === currentMonthName);
    case 'last-3-months':
      return allReportData.value.filter(item => {
        const itemMonthIndex = allMonths.indexOf(item.month);
        return itemMonthIndex >= currentMonthIndex - 2 && itemMonthIndex <= currentMonthIndex;
      });
    case 'last-6-months':
      return allReportData.value.filter(item => {
        const itemMonthIndex = allMonths.indexOf(item.month);
        return itemMonthIndex >= currentMonthIndex - 5 && itemMonthIndex <= currentMonthIndex;
      });
    case 'all':
    default:
      return allReportData.value;
  }
});

// Computed properties for totals
const totalData = computed(() => {
  if (filteredReportData.value.length === 0) {
    return {
      routingOffice: 'Total',
      jan: '0:00', feb: '0:00', mar: '0:00', apr: '0:00', may: '0:00', jun: '0:00',
      jul: '0:00', aug: '0:00', sep: '0:00', oct: '0:00', nov: '0:00', dec: '0:00',
      avgHours1stSem: 0, avgHours2ndSem: 0
    };
  }

  const parseTimeToMinutes = (timeStr) => {
    if (!timeStr || timeStr === '-') return 0;
    const cleanTime = timeStr.replace(/,/g, '');
    const parts = cleanTime.split(':');
    return parseInt(parts[0]) * 60 + parseInt(parts[1]);
  };

  const totals = filteredReportData.value.reduce(
    (acc, row) => {
      acc.jan += parseTimeToMinutes(row.jan);
      acc.feb += parseTimeToMinutes(row.feb);
      acc.mar += parseTimeToMinutes(row.mar);
      acc.apr += parseTimeToMinutes(row.apr);
      acc.may += parseTimeToMinutes(row.may);
      acc.jun += parseTimeToMinutes(row.jun);
      acc.jul += parseTimeToMinutes(row.jul);
      acc.aug += parseTimeToMinutes(row.aug);
      acc.sep += parseTimeToMinutes(row.sep);
      acc.oct += parseTimeToMinutes(row.oct);
      acc.nov += parseTimeToMinutes(row.nov);
      acc.dec += parseTimeToMinutes(row.dec);
      return acc;
    },
    { jan: 0, feb: 0, mar: 0, apr: 0, may: 0, jun: 0, jul: 0, aug: 0, sep: 0, oct: 0, nov: 0, dec: 0 }
  );

  const formatMinutesToHours = (minutes) => {
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;
    return hours > 0 ? `${hours.toLocaleString()}:${remainingMinutes.toString().padStart(2, '0')}` : `${remainingMinutes}:00`;
  };

  const totalAvg1stSem = filteredReportData.value.reduce((sum, row) => sum + row.avgHours1stSem, 0);
  const totalAvg2ndSem = filteredReportData.value.reduce((sum, row) => sum + row.avgHours2ndSem, 0);

  return {
    routingOffice: 'TOTAL',
    jan: formatMinutesToHours(totals.jan),
    feb: formatMinutesToHours(totals.feb),
    mar: formatMinutesToHours(totals.mar),
    apr: formatMinutesToHours(totals.apr),
    may: formatMinutesToHours(totals.may),
    jun: formatMinutesToHours(totals.jun),
    jul: formatMinutesToHours(totals.jul),
    aug: formatMinutesToHours(totals.aug),
    sep: formatMinutesToHours(totals.sep),
    oct: formatMinutesToHours(totals.oct),
    nov: formatMinutesToHours(totals.nov),
    dec: formatMinutesToHours(totals.dec),
    avgHours1stSem: totalAvg1stSem,
    avgHours2ndSem: totalAvg2ndSem,
  };
});

const currentDate = ref(new Date().toLocaleDateString('en-US', {
  year: 'numeric',
  month: 'long',
  day: 'numeric',
  hour: '2-digit',
  minute: '2-digit'
}))
</script>

<template>
  <div class=" bg-gray-50 p-2 sm:p-4 lg:p-6 xl:p-8 font-sans">
    <!-- Header Card -->
    <div class="bg-white rounded-lg shadow-sm border p-3 sm:p-4 lg:p-6 mb-4 sm:mb-6">
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div class="flex-1">
          <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold text-gray-900 leading-tight">
            Incoming Document Referral Summary
          </h2>
          <p class="text-xs sm:text-sm text-gray-600 mt-1 sm:mt-2">
            Time motion analysis of documents referred to various offices
          </p>
        </div>
        <div class="text-right w-full lg:w-auto">
          <p class="text-xs text-gray-600">Generated on: {{ currentDate }}</p>
          <p class="text-xs text-gray-600">Generated by: Kenneth Sayan</p>
        </div>
      </div>
    </div>

    <!-- Desktop Table (xl screens and up) -->
    <div class="hidden xl:block">
      <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th rowspan="2" class="px-4 py-4 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">
                  Routing Offices
                </th>
                <th colspan="6" class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">
                  1st Semester
                </th>
                <th rowspan="2" class="px-3 py-4 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">
                  Avg. Hours<br>1st Sem
                </th>
                <th colspan="6" class="px-4 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">
                  2nd Semester
                </th>
                <th rowspan="2" class="px-3 py-4 text-center text-xs font-medium text-gray-700 uppercase tracking-wider">
                  Avg. Hours<br>2nd Sem
                </th>
              </tr>
              <tr>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Jan</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Feb</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Mar</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Apr</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">May</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Jun</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Jul</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Aug</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Sep</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Oct</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Nov</th>
                <th class="px-3 py-2 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300">Dec</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="office in filteredReportData" :key="office.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm font-medium text-gray-900 border-r border-gray-300">{{ office.routingOffice }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.jan }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.feb }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.mar }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.apr }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.may }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.jun }}</td>
                <td class="px-3 py-3 text-sm text-center font-semibold text-blue-700 bg-blue-50 border-r border-gray-300">{{ office.avgHours1stSem }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.jul }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.aug }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.sep }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.oct }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.nov }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ office.dec }}</td>
                <td class="px-3 py-3 text-sm text-center font-semibold text-green-700 bg-green-50">{{ office.avgHours2ndSem }}</td>
              </tr>
              <tr class="bg-gray-100 font-bold border-t-2 border-gray-300">
                <td class="px-4 py-3 text-sm text-gray-900 border-r border-gray-300">{{ totalData.routingOffice }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.jan }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.feb }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.mar }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.apr }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.may }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.jun }}</td>
                <td class="px-3 py-3 text-sm text-center text-blue-700 bg-blue-100 border-r border-gray-300">{{ totalData.avgHours1stSem }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.jul }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.aug }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.sep }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.oct }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.nov }}</td>
                <td class="px-3 py-3 text-sm text-center text-gray-700 border-r border-gray-300">{{ totalData.dec }}</td>
                <td class="px-3 py-3 text-sm text-center text-green-700 bg-green-100">{{ totalData.avgHours2ndSem }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Tablet Horizontal Scroll Table (lg to xl) -->
    <div class="hidden lg:block xl:hidden">
      <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
          <div class="min-w-[1000px]">
            <table class="w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th rowspan="2" class="px-2 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300 w-24">
                    Office
                  </th>
                  <th colspan="6" class="px-2 py-2 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">
                    1st Semester
                  </th>
                  <th rowspan="2" class="px-1 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300 w-16">
                    Avg 1st
                  </th>
                  <th colspan="6" class="px-2 py-2 text-center text-xs font-medium text-gray-700 uppercase tracking-wider border-r border-gray-300">
                    2nd Semester
                  </th>
                  <th rowspan="2" class="px-1 py-3 text-center text-xs font-medium text-gray-700 uppercase tracking-wider w-16">
                    Avg 2nd
                  </th>
                </tr>
                <tr>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Jan</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Feb</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Mar</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Apr</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">May</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Jun</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Jul</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Aug</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Sep</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Oct</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Nov</th>
                  <th class="px-2 py-1 text-center text-xs font-medium text-gray-700 uppercase border-r border-gray-300 w-16">Dec</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="office in filteredReportData" :key="office.id" class="hover:bg-gray-50">
                  <td class="px-2 py-2 text-xs font-medium text-gray-900 border-r border-gray-300">{{ office.routingOffice }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.jan }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.feb }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.mar }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.apr }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.may }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.jun }}</td>
                  <td class="px-1 py-2 text-xs text-center font-semibold text-blue-700 bg-blue-50 border-r border-gray-300">{{ office.avgHours1stSem }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.jul }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.aug }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.sep }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.oct }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.nov }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ office.dec }}</td>
                  <td class="px-1 py-2 text-xs text-center font-semibold text-green-700 bg-green-50">{{ office.avgHours2ndSem }}</td>
                </tr>
                <tr class="bg-gray-100 font-bold border-t-2 border-gray-300">
                  <td class="px-2 py-2 text-xs text-gray-900 border-r border-gray-300">{{ totalData.routingOffice }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.jan }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.feb }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.mar }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.apr }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.may }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.jun }}</td>
                  <td class="px-1 py-2 text-xs text-center text-blue-700 bg-blue-100 border-r border-gray-300">{{ totalData.avgHours1stSem }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.jul }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.aug }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.sep }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.oct }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.nov }}</td>
                  <td class="px-1 py-2 text-xs text-center text-gray-700 border-r border-gray-300">{{ totalData.dec }}</td>
                  <td class="px-1 py-2 text-xs text-center text-green-700 bg-green-100">{{ totalData.avgHours2ndSem }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  
    <!-- Mobile/Tablet Portrait Cards (below lg) -->
    <div class="block lg:hidden">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-lg border p-4 text-center">
          <div class="text-2xl font-bold text-blue-600">{{ filteredReportData.length }}</div>
          <div class="text-sm text-gray-600">Routing Offices</div>
        </div>
        <div class="bg-white rounded-lg border p-4 text-center">
          <div class="text-2xl font-bold text-green-600">{{ totalData.avgHours1stSem + totalData.avgHours2ndSem }}</div>
          <div class="text-sm text-gray-600">Total Avg Hours</div>
        </div>
      </div>

      <!-- Office Cards -->
      <div class="space-y-4 sm:space-y-6">
        <div v-for="office in filteredReportData" :key="office.id" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
          <!-- Office Header -->
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4">
            <div class="flex items-center">
              <ClockIcon class="w-6 h-6 text-white mr-3 flex-shrink-0" />
              <h3 class="text-lg font-semibold text-white">{{ office.routingOffice }}</h3>
            </div>
          </div>

          <!-- Content -->
          <div class="p-4 space-y-6">
            <!-- 1st Semester -->
            <div>
              <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                1st Semester
              </h4>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 mb-4">
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Jan</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.jan }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Feb</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.feb }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Mar</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.mar }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Apr</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.apr }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">May</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.may }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Jun</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.jun }}</div>
                </div>
              </div>
              <div class="bg-blue-50 border-l-4 border-blue-400 p-3 rounded-lg">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-xs text-blue-600 font-medium">Average Hours (1st Semester)</div>
                    <div class="text-lg font-bold text-blue-700">{{ office.avgHours1stSem }} hours</div>
                  </div>
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold">1st</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- 2nd Semester -->
            <div>
              <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                2nd Semester
              </h4>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3 mb-4">
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Jul</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.jul }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Aug</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.aug }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Sep</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.sep }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Oct</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.oct }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Nov</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.nov }}</div>
                </div>
                <div class="bg-gray-50 p-2 sm:p-3 rounded-lg">
                  <div class="text-xs text-gray-500 font-medium">Dec</div>
                  <div class="text-sm sm:text-base font-semibold text-gray-900">{{ office.dec }}</div>
                </div>
              </div>
              <div class="bg-green-50 border-l-4 border-green-400 p-3 rounded-lg">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-xs text-green-600 font-medium">Average Hours (2nd Semester)</div>
                    <div class="text-lg font-bold text-green-700">{{ office.avgHours2ndSem }} hours</div>
                  </div>
                  <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-green-600 font-bold">2nd</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Summary Card -->
        <div class="bg-gradient-to-br from-gray-800 to-gray-900 text-white rounded-lg shadow-lg overflow-hidden">
          <!-- Header -->
          <div class="bg-gradient-to-r from-gray-700 to-gray-800 p-4">
            <h3 class="text-lg font-bold flex items-center">
              <span class="w-6 h-6 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                <span class="text-xs font-bold">∑</span>
              </span>
              TOTAL SUMMARY
            </h3>
          </div>

          <!-- Content -->
          <div class="p-4 space-y-4">
            <!-- Monthly Data in Compact Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 text-xs">
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Jan</div>
                <div class="font-semibold">{{ totalData.jan }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Feb</div>
                <div class="font-semibold">{{ totalData.feb }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Mar</div>
                <div class="font-semibold">{{ totalData.mar }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Apr</div>
                <div class="font-semibold">{{ totalData.apr }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">May</div>
                <div class="font-semibold">{{ totalData.may }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Jun</div>
                <div class="font-semibold">{{ totalData.jun }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Jul</div>
                <div class="font-semibold">{{ totalData.jul }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Aug</div>
                <div class="font-semibold">{{ totalData.aug }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Sep</div>
                <div class="font-semibold">{{ totalData.sep }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Oct</div>
                <div class="font-semibold">{{ totalData.oct }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Nov</div>
                <div class="font-semibold">{{ totalData.nov }}</div>
              </div>
              <div class="bg-white bg-opacity-10 p-2 rounded">
                <div class="text-gray-300">Dec</div>
                <div class="font-semibold">{{ totalData.dec }}</div>
              </div>
            </div>

            <!-- Average Summary -->
            <div class="border-t border-gray-600 pt-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-blue-500 bg-opacity-20 border border-blue-400 border-opacity-30 p-3 rounded-lg">
                  <div class="text-xs text-blue-200 font-medium">Average Hours 1st Semester</div>
                  <div class="text-xl font-bold text-blue-100">{{ totalData.avgHours1stSem }}</div>
                </div>
                <div class="bg-green-500 bg-opacity-20 border border-green-400 border-opacity-30 p-3 rounded-lg">
                  <div class="text-xs text-green-200 font-medium">Average Hours 2nd Semester</div>
                  <div class="text-xl font-bold text-green-100">{{ totalData.avgHours2ndSem }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>