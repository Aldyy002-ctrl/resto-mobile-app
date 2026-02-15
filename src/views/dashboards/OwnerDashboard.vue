<template>
  <ion-page>
    <!-- Mobile Header -->
    <ion-header>
      <ion-toolbar color="dark">
        <ion-buttons slot="start">
          <ion-menu-button></ion-menu-button>
        </ion-buttons>
        <ion-title>Dashboard Owner</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="showDatePicker">
            <ion-icon :icon="calendarOutline"></ion-icon>
          </ion-button>
          <ion-button color="danger" @click="handleLogout">
            <ion-icon slot="icon-only" :icon="logOutOutline"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <!-- Main Content -->
    <ion-content :fullscreen="true">
      <!-- Date Filter Banner (Collapsible) -->
      <div v-if="showDateFilter" class="date-filter-mobile">
        <div class="filter-row">
          <ion-label>Dari:</ion-label>
          <input type="date" v-model="filters.startDate" class="date-input-mobile" />
        </div>
        <div class="filter-row">
          <ion-label>Sampai:</ion-label>
          <input type="date" v-model="filters.endDate" class="date-input-mobile" />
        </div>
        <ion-button expand="block" @click="loadReports" :disabled="loading">
          {{ loading ? 'Memuat...' : 'Tampilkan Data' }}
        </ion-button>
      </div>

      <!-- Summary Cards - Horizontal Scroll -->
      <div class="summary-section">
        <h2 class="section-title">Ringkasan</h2>
        <div class="summary-scroll-container">
          <div class="summary-card-mobile revenue-card">
            <div class="card-icon">💰</div>
            <div class="card-content">
              <div class="card-label">Total Pendapatan</div>
              <div class="card-value">{{ formatCurrency(reports.total_revenue) }}</div>
              <div class="card-period">{{ formatDateRange() }}</div>
            </div>
          </div>

          <div class="summary-card-mobile orders-card">
            <div class="card-icon">🛍️</div>
            <div class="card-content">
              <div class="card-label">Total Transaksi</div>
              <div class="card-value">{{ reports.total_orders }}</div>
              <div class="card-period">Order</div>
            </div>
          </div>

          <div class="summary-card-mobile bestseller-card">
            <div class="card-icon">⭐</div>
            <div class="card-content">
              <div class="card-label">Menu Terlaris</div>
              <div class="card-value">{{ reports.best_seller?.nama_menu || '-' }}</div>
              <div class="card-period">{{ reports.best_seller?.total_terjual || 0 }} terjual</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Revenue Chart -->
      <ion-card>
        <ion-card-header>
          <ion-card-title>📊 Grafik Pendapatan</ion-card-title>
          <ion-card-subtitle>Harian</ion-card-subtitle>
        </ion-card-header>
        <ion-card-content>
          <div class="chart-wrapper-mobile">
            <canvas ref="revenueChartCanvas"></canvas>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Employee Performance -->
      <ion-card>
        <ion-card-header>
          <ion-card-title>👥 Kinerja Karyawan</ion-card-title>
          <ion-card-subtitle>Top 5 Performers</ion-card-subtitle>
        </ion-card-header>
        <ion-card-content>
          <ion-list v-if="reports.top_employees?.length > 0" lines="full">
            <ion-item v-for="(emp, idx) in reports.top_employees" :key="idx">
              <ion-avatar slot="start">
                <div class="avatar-text">{{ emp.username.charAt(0).toUpperCase() }}</div>
              </ion-avatar>
              <ion-label>
                <h3>{{ emp.username }}</h3>
                <p>{{ emp.role }} • {{ emp.order_count }} orders</p>
              </ion-label>
              <ion-note slot="end" color="success">
                {{ formatCurrency(emp.revenue) }}
              </ion-note>
            </ion-item>
          </ion-list>
          <div v-else class="empty-state-mobile">
            <ion-icon :icon="peopleOutline" size="large"></ion-icon>
            <p>Belum ada data karyawan</p>
          </div>
        </ion-card-content>
      </ion-card>

      <!-- Spacing for FAB -->
      <div style="height: 80px;"></div>
    </ion-content>

    <!-- Floating Action Button -->
    <ion-fab vertical="bottom" horizontal="end" slot="fixed">
      <ion-fab-button color="success" @click="handlePrint">
        <ion-icon :icon="printOutline"></ion-icon>
      </ion-fab-button>
    </ion-fab>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent, IonButtons, 
  IonButton, IonMenuButton, IonIcon, IonCard, IonCardHeader, 
  IonCardTitle, IonCardSubtitle, IonCardContent, IonList, IonItem, 
  IonLabel, IonNote, IonAvatar, IonFab, IonFabButton,
  alertController
} from '@ionic/vue';
import { 
  calendarOutline, printOutline, peopleOutline, logOutOutline 
} from 'ionicons/icons';
import { useRouter } from 'vue-router';
import authService from '@/services/auth';
import { Chart, registerables } from 'chart.js';
import api from '@/services/api';

Chart.register(...registerables);

const router = useRouter();

// State
const loading = ref(false);
const showDateFilter = ref(false);
const filters = ref({
  startDate: new Date(Date.now() - 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
  endDate: new Date().toISOString().split('T')[0]
});

const reports = ref({
  total_revenue: 0,
  total_orders: 0,
  best_seller: null as any,
  daily_revenue: [] as any[],
  top_employees: [] as any[]
});

const revenueChartCanvas = ref<HTMLCanvasElement | null>(null);
let revenueChart: Chart | null = null;

// Load reports
const loadReports = async () => {
  loading.value = true;
  showDateFilter.value = false; // Close filter after load
  
  try {
    // Get summary
    const summaryRes = await api.get('/reports.php', {
      params: {
        type: 'summary',
        date_from: filters.value.startDate,
        date_to: filters.value.endDate
      }
    });

    // Get revenue data
    const revenueRes = await api.get('/reports.php', {
      params: {
        type: 'revenue',
        period: 'daily',
        date_from: filters.value.startDate,
        date_to: filters.value.endDate
      }
    });

    // Get top menu
    const topMenuRes = await api.get('/reports.php', {
      params: {
        type: 'top-menu',
        limit: 1,
        date_from: filters.value.startDate,
        date_to: filters.value.endDate
      }
    });

    // Extract data
    const summaryData = (summaryRes.data as any)?.data || summaryRes.data;
    const revenueData = (revenueRes.data as any)?.data?.data || [];
    const topMenuData = (topMenuRes.data as any)?.data || [];

    reports.value = {
      total_revenue: summaryData.total_pendapatan || 0,
      total_orders: summaryData.total_transaksi || 0,
      best_seller: topMenuData[0] || null,
      daily_revenue: revenueData,
      top_employees: []
    };

    updateChart();
  } catch (error) {
    console.error('Failed to load reports:', error);
  } finally {
    loading.value = false;
  }
};

// Update chart
const updateChart = () => {
  if (!revenueChartCanvas.value) return;

  if (revenueChart) {
    revenueChart.destroy();
  }

  const ctx = revenueChartCanvas.value.getContext('2d');
  if (!ctx) return;

  const labels = reports.value.daily_revenue.map(d => {
    const date = new Date(d.tanggal || d.bulan || d.tahun);
    return date.toLocaleDateString('id-ID', { month: 'short', day: 'numeric' });
  });
  const data = reports.value.daily_revenue.map(d => parseFloat(d.total_pendapatan) || 0);

  revenueChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Pendapatan',
        data,
        fill: true,
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        borderColor: '#10b981',
        borderWidth: 2,
        tension: 0.4,
        pointBackgroundColor: '#10b981',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 3,
        pointHoverRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 1.5,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: '#1e293b',
          titleColor: '#f1f5f9',
          bodyColor: '#cbd5e1',
          padding: 12,
          displayColors: false,
          callbacks: {
            label: (context) => `Rp ${context.parsed.y.toLocaleString('id-ID')}`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(255, 255, 255, 0.05)'
          },
          ticks: {
            color: '#94a3b8',
            callback: (value) => {
              const num = value as number;
              return num >= 1000 ? `${(num / 1000).toFixed(0)}k` : num;
            }
          }
        },
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#94a3b8',
            maxRotation: 45,
            minRotation: 0
          }
        }
      }
    }
  });
};

// Helper functions
const formatCurrency = (value: number) => {
  if (!value) return 'Rp 0';
  return `Rp ${value.toLocaleString('id-ID')}`;
};

const formatDateRange = () => {
  const start = new Date(filters.value.startDate);
  const end = new Date(filters.value.endDate);
  return `${start.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })} - ${end.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}`;
};

const showDatePicker = () => {
  showDateFilter.value = !showDateFilter.value;
};

const handlePrint = async () => {
  const alert = await alertController.create({
    header: 'Cetak Laporan',
    message: 'Fitur cetak laporan akan tersedia segera.',
    buttons: ['OK']
  });
  await alert.present();
};

const handleLogout = () => {
  authService.logout();
  router.push('/login');
};

// Lifecycle
onMounted(() => {
  loadReports();
});

onUnmounted(() => {
  if (revenueChart) {
    revenueChart.destroy();
  }
});
</script>

<style scoped>
/* Mobile-first styles */
ion-content {
  --background: #0f172a;
}

ion-toolbar {
  --background: #1e293b;
  --color: #f1f5f9;
}

ion-card {
  --background: #1e293b;
  --color: #e2e8f0;
  margin: 1rem;
  border-radius: 12px;
}

ion-card-title {
  color: #f1f5f9;
  font-size: 1.1rem;
  font-weight: 600;
}

ion-card-subtitle {
  color: #94a3b8;
  font-size: 0.875rem;
}

/* Date Filter */
.date-filter-mobile {
  background: #1e293b;
  padding: 1rem;
  margin: 1rem;
  border-radius: 12px;
  border: 1px solid #334155;
}

.filter-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.filter-row ion-label {
  min-width: 60px;
  color: #cbd5e1;
  font-weight: 500;
}

.date-input-mobile {
  flex: 1;
  background: #0f172a;
  border: 1px solid #334155;
  color: #e2e8f0;
  padding: 0.625rem;
  border-radius: 8px;
  font-size: 0.9rem;
}

/* Summary Section */
.summary-section {
  padding: 0.5rem 0.75rem;
}

.section-title {
  margin: 0 0 0.625rem 0;
  color: #f1f5f9;
  font-size: 1rem;
  font-weight: 700;
}

.summary-scroll-container {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
  -webkit-overflow-scrolling: touch;
  scroll-snap-type: x mandatory;
  padding-right: 0.5rem;
}

.summary-scroll-container::-webkit-scrollbar {
  display: none;
}

.summary-card-mobile {
  min-width: 200px;
  max-width: 200px;
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border: 1px solid #334155;
  border-radius: 10px;
  padding: 0.75rem;
  display: flex;
  gap: 0.5rem;
  flex-shrink: 0;
  scroll-snap-align: start;
}

.revenue-card {
  border-left: 3px solid #10b981;
}

.orders-card {
  border-left: 3px solid #3b82f6;
}

.bestseller-card {
  border-left: 3px solid #f59e0b;
}

.card-icon {
  font-size: 1.5rem;
  opacity: 0.9;
  flex-shrink: 0;
}

.card-content {
  flex: 1;
  min-width: 0;
}

.card-label {
  color: #94a3b8;
  font-size: 0.65rem;
  font-weight: 500;
  margin-bottom: 0.25rem;
  text-transform: uppercase;
  letter-spacing: 0.2px;
}

.card-value {
  font-size: 1rem;
  font-weight: 700;
  color: #f1f5f9;
  margin-bottom: 0.2rem;
  line-height: 1.1;
  word-break: break-word;
}

.card-period {
  color: #64748b;
  font-size: 0.6rem;
}

/* Chart */
.chart-wrapper-mobile {
  height: 200px;
  position: relative;
}

/* Cards */
ion-card {
  --background: #1e293b;
  --color: #e2e8f0;
  margin: 0.75rem;
  border-radius: 12px;
}

/* Employee List */
ion-list {
  background: transparent;
}

ion-item {
  --background: transparent;
  --color: #e2e8f0;
  --border-color: #334155;
  --padding-start: 0;
  --inner-padding-end: 0;
}

.avatar-text {
  width: 100%;
  height: 100%;
  background: #8b5cf6;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.1rem;
}

ion-label h3 {
  color: #f1f5f9;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

ion-label p {
  color: #94a3b8;
  font-size: 0.8rem;
}

ion-note {
  font-weight: 700;
  font-size: 0.9rem;
}

/* Empty State */
.empty-state-mobile {
  text-align: center;
  padding: 2rem 1rem;
  color: #64748b;
}

.empty-state-mobile ion-icon {
  font-size: 3rem;
  margin-bottom: 0.5rem;
  opacity: 0.5;
}

/* FAB */
ion-fab-button {
  --background: #10b981;
  --background-activated: #059669;
  --box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Tablet and up */
@media (min-width: 768px) {
  .summary-scroll-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    overflow-x: visible;
  }

  .summary-card-mobile {
    min-width: auto;
  }

  .chart-wrapper-mobile {
    height: 300px;
  }
  
  .date-filter-mobile {
    max-width: 600px;
  }
  
  ion-card {
    margin: 1.5rem;
  }
}

/* Desktop and large screens */
@media (min-width: 1024px) {
  .summary-section {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  ion-card {
    max-width: 1200px;
    margin: 1.5rem auto;
  }
  
  .chart-wrapper-mobile {
    height: 350px;
  }
  
  .summary-card-mobile {
    padding: 1.5rem;
  }
  
  .card-value {
    font-size: 1.75rem;
  }
}

/* Extra large screens */
@media (min-width: 1440px) {
  .chart-wrapper-mobile {
    height: 400px;
  }
}
</style>
