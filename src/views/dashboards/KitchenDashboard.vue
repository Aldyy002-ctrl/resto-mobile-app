<template>
  <ion-page>
    <!-- Mobile Header -->
    <ion-header>
      <ion-toolbar color="dark">
        <ion-title>🔥 Antrian Dapur</ion-title>
        <ion-buttons slot="end">
          <ion-button @click="openStockModal">
            <ion-icon :icon="cubeOutline"></ion-icon>
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
      <ion-toolbar color="dark">
        <div class="user-toolbar">
          <div class="user-info">
            <ion-icon :icon="personCircleOutline"></ion-icon>
            <span>{{ currentUser?.username }}</span>
            <ion-badge color="warning">{{ currentUser?.role }}</ion-badge>
          </div>
          <ion-button size="small" color="danger" @click="handleLogout">
            Logout
          </ion-button>
        </div>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true">

      <!-- Order Queue -->
      <div class="orders-container">
        <div v-if="loading" class="loading">
          <ion-spinner name="crescent"></ion-spinner>
          <p>Memuat pesanan...</p>
        </div>

        <div v-else-if="orders.length === 0" class="empty-state">
          <div class="empty-icon">🍳</div>
          <h3>Tidak ada pesanan</h3>
          <p>Semua pesanan sudah selesai!</p>
        </div>

        <div v-else class="order-grid">
          <div v-for="order in orders" :key="order.id_transaksi" class="order-card">
            <div class="order-header">
              <h3>Order #{{ order.id_transaksi }}</h3>
              <span class="status-badge cooking">🔥 COOKING</span>
            </div>

            <div class="order-info">
              <div class="info-row">
                <span class="label">Meja:</span>
                <span class="value">{{ order.nomor_meja || 'Takeaway' }}</span>
              </div>
              <div class="info-row">
                <span class="label">Mulai:</span>
                <span class="value">{{ formatTime(order.tanggal_transaksi) }}</span>
              </div>
              <div class="info-row timer-row">
                <span class="label">⏱️ Durasi:</span>
                <span class="value timer-value">{{ getElapsedTime(order.tanggal_transaksi) }}</span>
              </div>
            </div>

            <div class="order-items">
              <h4>Item Pesanan:</h4>
              <div v-for="(item, idx) in parseOrderItems(order.items)" :key="idx" class="item-row">
                <span class="qty">{{ item.qty }}x</span>
                <span class="name">{{ item.nama_menu }}</span>
              </div>
            </div>

            <div class="order-actions">
              <button @click="viewOrderDetail(order)" class="btn-detail" title="Lihat Detail">
                👁️ Detail
              </button>
              <button @click="markDone(order)" class="btn-done" :disabled="processing">
                ✓ Selesai
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Stock Modal -->
      <ion-modal :is-open="stockModal.isOpen" @didDismiss="closeStockModal">
        <div class="stock-modal">
          <div class="modal-header">
            <h2>📦 Stok Bahan / Menu</h2>
            <button @click="closeStockModal" class="btn-close">✕</button>
          </div>

          <div class="stock-grid">
            <div v-for="item in menuItems" :key="item.id_menu" class="stock-item">
              <div class="stock-image">
                <img :src="item.foto_makanan || 'https://via.placeholder.com/80'" :alt="item.nama_menu" />
              </div>
              <div class="stock-info">
                <div class="menu-name">{{ item.nama_menu }}</div>
                <div class="stock-row">
                  <span class="stock-label">Sisa Stok</span>
                  <span class="stock-value">{{ item.stok }}</span>
                </div>
                <span 
                  class="stock-status" 
                  :class="getStockStatusClass(item.stok)"
                >
                  {{ getStockStatus(item.stok) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </ion-modal>

      <!-- Order Detail Modal -->
      <ion-modal :is-open="detailModal.isOpen" @didDismiss="closeDetailModal">
        <div class="detail-modal-container">
          <div class="modal-header">
            <h2>📋 Detail Pesanan #{{ detailModal.order?.id_transaksi }}</h2>
            <button @click="closeDetailModal" class="btn-close">✕</button>
          </div>
          
          <div class="modal-body-detail">
            <div class="detail-section">
              <h4>Informasi Pesanan</h4>
              <div class="info-grid">
                <div class="info-item">
                  <span class="label">ID Transaksi:</span>
                  <span class="value">#{{ detailModal.order?.id_transaksi }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Meja:</span>
                  <span class="value">{{ detailModal.order?.nomor_meja || 'Takeaway' }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Pelanggan:</span>
                  <span class="value">{{ detailModal.order?.nama_pelanggan || '-' }}</span>
                </div>
                <div class="info-item">
                  <span class="label">Waktu Mulai:</span>
                  <span class="value">{{ formatTime(detailModal.order?.tanggal_transaksi) }}</span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h4>Items yang Harus Dimasak</h4>
              <div class="items-detail">
                <div v-for="(item, idx) in parseOrderItems(detailModal.order?.items)" :key="idx" class="item-detail-row">
                  <span class="item-qty">{{ item.qty }}x</span>
                  <span class="item-name">{{ item.nama_menu }}</span>
                  <span class="item-price">Rp {{ (item.harga * item.qty).toLocaleString() }}</span>
                </div>
              </div>
            </div>

            <div v-if="detailModal.order?.catatan" class="detail-section">
              <h4>Catatan Khusus</h4>
              <p class="notes-text">⚠️ {{ detailModal.order.catatan }}</p>
            </div>

            <div class="detail-section">
              <div class="total-row">
                <span class="total-label">Total Pesanan:</span>
                <span class="total-value">Rp {{ Number(detailModal.order?.total_harga || 0).toLocaleString() }}</span>
              </div>
            </div>
          </div>
        </div>
      </ion-modal>

      <!-- Notification Toast -->
      <ion-toast
        :is-open="notification.show"
        :message="notification.message"
        :duration="3000"
        :color="notification.type"
        @didDismiss="notification.show = false"
      ></ion-toast>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { 
  IonPage, IonHeader, IonToolbar, IonTitle, IonButtons, IonButton,
  IonContent, IonModal, IonSpinner, IonToast, IonBadge, IonIcon
} from '@ionic/vue';
import { cubeOutline, personCircleOutline } from 'ionicons/icons';
import { useRouter } from 'vue-router';
import { ordersApi, menuApi } from '@/services/restaurant';
import authService from '@/services/auth';

const router = useRouter();

// State
const currentUser = ref(authService.getCurrentUser());
const orders = ref<any[]>([]);
const menuItems = ref<any[]>([]);
const loading = ref(true);
const processing = ref(false);
const stockModal = ref({ isOpen: false });
const notification = ref({
  show: false,
  message: '',
  type: 'success' as 'success' | 'danger' | 'warning'
});

let refreshInterval: any = null;
let timerInterval: any = null;
const currentTime = ref(Date.now());

const detailModal = ref({
  isOpen: false,
  order: null as any
});

// Load orders
const loadOrders = async () => {
  try {
    const response = await ordersApi.getAll();
    // Handle response structure - extract actual data array
    const ordersData = Array.isArray(response.data) ? response.data : 
                       (response.data as any)?.data || [];
    
    // Filter only orders with status "cooking"
    orders.value = ordersData.filter((order: any) => 
      order.status === 'cooking'
    );
  } catch (error) {
    console.error('Failed to load orders:', error);
  } finally {
    loading.value = false;
  }
};

// Load menu items for stock modal
const loadMenu = async () => {
  try {
    const response = await menuApi.getAll();
    // Handle response structure - extract actual data array
    menuItems.value = Array.isArray(response.data) ? response.data : 
                      (response.data as any)?.data || [];
  } catch (error) {
    console.error('Failed to load menu:', error);
  }
};

// Parse order items from JSON string
const parseOrderItems = (itemsJson: string) => {
  try {
    return JSON.parse(itemsJson);
  } catch {
    return [];
  }
};

// Format time
const formatTime = (datetime: string) => {
  if (!datetime) return '-';
  const date = new Date(datetime);
  return date.toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit' 
  });
};

// Calculate elapsed time from order start
const getElapsedTime = (startTime: string) => {
  if (!startTime) return '0:00';
  
  const start = new Date(startTime).getTime();
  const now = currentTime.value;
  const diff = Math.floor((now - start) / 1000); // seconds
  
  const hours = Math.floor(diff / 3600);
  const minutes = Math.floor((diff % 3600) / 60);
  const seconds = diff % 60;
  
  if (hours > 0) {
    return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  }
  return `${minutes}:${seconds.toString().padStart(2, '0')}`;
};

// View order detail
const viewOrderDetail = (order: any) => {
  detailModal.value.order = order;
  detailModal.value.isOpen = true;
};

const closeDetailModal = () => {
  detailModal.value.isOpen = false;
  detailModal.value.order = null;
};

// Mark order as done
const markDone = async (order: any) => {
  if (!confirm(`Tandai Order #${order.id_transaksi} sebagai selesai?`)) return;

  processing.value = true;
  try {
    await ordersApi.updateStatus(order.id_transaksi, 'ready');
    showNotification('Pesanan berhasil diselesaikan!', 'success');
    await loadOrders(); // Refresh orders
  } catch (error) {
    showNotification('Gagal menyelesaikan pesanan', 'danger');
  } finally {
    processing.value = false;
  }
};

// Stock modal functions
const openStockModal = async () => {
  await loadMenu();
  stockModal.value.isOpen = true;
};

const closeStockModal = () => {
  stockModal.value.isOpen = false;
};

// Stock status helpers
const getStockStatus = (stok: number) => {
  if (stok > 50) return 'Aman';
  if (stok > 20) return 'Cukup';
  if (stok > 0) return 'Rendah';
  return 'Habis';
};

const getStockStatusClass = (stok: number) => {
  if (stok > 50) return 'status-aman';
  if (stok > 20) return 'status-cukup';
  if (stok > 0) return 'status-rendah';
  return 'status-habis';
};

// Notification
const showNotification = (message: string, type: 'success' | 'danger' | 'warning' = 'success') => {
  notification.value = {
    show: true,
    message,
    type
  };
};

// Logout
const handleLogout = () => {
  authService.logout();
  router.push('/tabs/tab1');
};

// Lifecycle
onMounted(() => {
  loadOrders();
  // Auto-refresh every 5 seconds
  refreshInterval = setInterval(loadOrders, 5000);
  // Update timer every second
  timerInterval = setInterval(() => {
    currentTime.value = Date.now();
  }, 1000);
});

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
  if (timerInterval) {
    clearInterval(timerInterval);
  }
});
</script>

<style scoped>
/* Mobile-first Kitchen Dashboard */
ion-content {
  --background: #0f172a;
}

ion-toolbar {
  --background: #1e293b;
  --color: #f1f5f9;
  --border-color: #334155;
}

.user-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #cbd5e1;
  font-size: 0.9rem;
}

.user-info ion-icon {
  font-size: 1.5rem;
  color: #8b5cf6;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.btn-stock {
  background: #8b5cf6;
  color: white;
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-stock:hover {
  background: #7c3aed;
  transform: translateY(-1px);
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #334155;
  border-radius: 8px;
}

.role-badge {
  padding: 0.25rem 0.75rem;
  background: #f97316;
  color: white;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.btn-logout {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-logout:hover {
  background: #dc2626;
}

/* Orders Container */
.orders-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.loading {
  text-align: center;
  padding: 3rem;
  color: #94a3b8;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #94a3b8;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #cbd5e1;
  margin-bottom: 0.5rem;
}

/* Order Grid */
.order-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.order-card {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s;
}

.order-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 8px 16px rgba(59, 130, 246, 0.1);
  transform: translateY(-2px);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #334155;
}

.order-header h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #f1f5f9;
}

.status-badge {
  padding: 0.375rem 0.875rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.status-badge.cooking {
  background: #3b82f6;
  color: white;
}

.order-info {
  margin-bottom: 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: #cbd5e1;
}

.info-row .label {
  color: #94a3b8;
  font-weight: 600;
}

.timer-row {
  background: rgba(59, 130, 246, 0.1);
  padding: 8px 12px;
  border-radius: 6px;
  margin-top: 8px;
}

.timer-row .label {
  color: #3b82f6;
  font-weight: 700;
}

.timer-value {
  color: #3b82f6 !important;
  font-weight: 700;
  font-size: 1.125rem;
  font-family: 'Courier New', monospace;
}

.order-items {
  background: #0f172a;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.order-items h4 {
  margin: 0 0 0.75rem 0;
  font-size: 0.875rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.item-row {
  display: flex;
  gap: 0.5rem;
  padding: 0.5rem 0;
  border-bottom: 1px solid #1e293b;
}

.item-row:last-child {
  border-bottom: none;
}

.item-row .qty {
  color: #3b82f6;
  font-weight: 700;
  min-width: 3rem;
}

.item-row .name {
  color: #e2e8f0;
}

/* Order Actions */
.order-actions {
  display: flex;
  gap: 10px;
  margin-top: 16px;
}

.btn-detail {
  flex: 1;
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  color: white;
  border: none;
  padding: 14px 20px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.btn-detail:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.btn-detail:active {
  transform: translateY(0);
}

.btn-done {
  width: 100%;
  background: #10b981;
  color: white;
  border: none;
  padding: 0.875rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-done:hover:not(:disabled) {
  background: #059669;
  transform: translateY(-1px);
}

.btn-done:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Stock Modal */
.stock-modal {
  background: #0f172a;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.modal-header {
  background: #1e293b;
  padding: 1.5rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #334155;
}

.modal-header h2 {
  margin: 0;
  color: #f1f5f9;
  font-size: 1.5rem;
}

.btn-close {
  background: #334155;
  color: #e2e8f0;
  border: none;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 8px;
  font-size: 1.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #ef4444;
  color: white;
}

.stock-grid {
  padding: 2rem;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  overflow-y: auto;
  flex: 1;
}

.stock-item {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 1.25rem;
  display: flex;
  gap: 1rem;
  transition: all 0.2s;
}

.stock-item:hover {
  border-color: #8b5cf6;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.1);
}

.stock-image {
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
}

.stock-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.stock-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.menu-name {
  font-weight: 700;
  color: #f1f5f9;
  font-size: 1rem;
}

.stock-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stock-label {
  color: #94a3b8;
  font-size: 0.875rem;
}

.stock-value {
  color: #cbd5e1;
  font-weight: 700;
  font-size: 1.125rem;
}

.stock-status {
  padding: 0.375rem 0.875rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  display: inline-block;
  margin-top: 0.25rem;
}

.status-aman {
  background: #10b981;
  color: white;
}

.status-cukup {
  background: #f59e0b;
  color: white;
}

.status-rendah {
  background: #ef4444;
  color: white;
}

/* Detail Modal Popup Overlay */
.detail-modal-container {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  background: linear-gradient(to bottom, #1e293b 0%, #0f172a 100%);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  border: 1px solid #334155;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.detail-modal-container .modal-header {
  flex-shrink: 0;
}

.modal-body-detail {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem;
}

.detail-section {
  margin-bottom: 1.25rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #334155;
}

.detail-section:last-child {
  border-bottom: none;
}

.detail-section h4 {
  font-size: 0.875rem;
  font-weight: 600;
  color: #94a3b8;
  margin: 0 0 0.75rem 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-item .label {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
}

.info-item .value {
  font-size: 0.875rem;
  color: #cbd5e1;
  font-weight: 600;
}

.items-detail {
  background: #0f172a;
  border-radius: 8px;
  padding: 0.75rem;
}

.item-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #1e293b;
}

.item-detail-row:last-child {
  border-bottom: none;
}

.item-qty {
  background: #8b5cf6;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-weight: 600;
  font-size: 0.75rem;
  min-width: 45px;
  text-align: center;
}

.item-name {
  flex: 1;
  margin: 0 0.75rem;
  color: #cbd5e1;
  font-size: 0.875rem;
}

.item-price {
  color: #10b981;
  font-weight: 600;
  font-size: 0.875rem;
}

.notes-text {
  background: rgba(251, 191, 36, 0.1);
  border-left: 3px solid #fbbf24;
  padding: 0.75rem;
  border-radius: 6px;
  color: #fcd34d;
  font-size: 0.875rem;
  margin: 0;
  line-height: 1.6;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(139, 92, 246, 0.1);
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #8b5cf6;
}

.total-label {
  font-size: 0.875rem;
  color: #94a3b8;
  font-weight: 600;
}

.total-value {
  font-size: 1.125rem;
  color: #10b981;
  font-weight: 700;
}

@media (max-width: 640px) {
  .detail-modal-container {
    width: 95%;
  }
  
  .info-grid {
    grid-template-columns: 1fr;
  }
}

.status-habis {
  background: #64748b;
  color: white;
}

/* Responsive */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }

  .header-right {
    flex-wrap: wrap;
    justify-content: center;
  }

  .order-grid {
    grid-template-columns: 1fr;
  }

  .stock-grid {
    grid-template-columns: 1fr;
    padding: 1rem;
  }
}
</style>
