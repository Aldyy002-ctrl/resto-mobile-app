<template>
  <ion-page>
    <div class="dashboard-container">
      <!-- Header -->
      <div class="header">
        <h1>Dashboard Kasir</h1>
        <button @click="handleLogout" class="logout-btn">Logout</button>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <!-- Left: Pesanan Masuk -->
        <div class="orders-section">
          <h2>📋 Pesanan Masuk (Pending)</h2>
          
          <div v-if="pendingOrders.length === 0" class="empty-state">
            <p>Tidak ada pesanan pending</p>
          </div>

          <table v-else class="orders-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Meja</th>
                <th>Items</th>
                <th>Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in pendingOrders" :key="order.id_transaksi">
                <td>
                  <span class="order-id">#{{ order.id_transaksi }}</span>
                </td>
                <td>
                  <span class="table-badge">Meja {{ order.nomor_meja }}</span>
                </td>
                <td>
                  <div class="items-list">
                    {{ order.items_count || '1x' }} {{ order.items_summary }}
                  </div>
                </td>
                <td>
                  <span class="total-price">Rp {{ Number(order.total_harga).toLocaleString() }}</span>
                </td>
                <td>
                  <div class="action-buttons">
                    <button @click.stop="viewOrderDetail(order)" class="detail-btn" title="Lihat Detail">👁️</button>
                    <button @click.stop="openPaymentModal(order)" class="pay-btn">Bayar</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Right: Status Meja -->
        <div class="tables-section">
          <h2>Status Meja</h2>
          
          <div class="tables-grid">
            <div 
              v-for="table in tables" 
              :key="table.id_meja"
              :class="['table-card', table.status]"
              @click="handleTableClick(table)"
            >
              <div class="table-number">Meja {{ table.nomor_meja }}</div>
              <div class="table-status">{{ table.status === 'kosong' ? 'KOSONG' : 'ISI' }}</div>
            </div>
          </div>

          <div class="status-legend">
            <p>Klik "ISI" untuk menggantikan status pesanan.</p>
          </div>
        </div>
      </div>

      <!-- Payment Modal -->
      <div v-if="paymentModal.isOpen" class="modal-overlay" @click.stop="closePaymentModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Konfirmasi Pembayaran</h3>
            <button @click="closePaymentModal" class="close-btn">×</button>
          </div>
          
          <div class="modal-body">
            <div class="total-section">
              <span>Total Tagihan</span>
              <h2 class="total-amount">Rp {{ Number(paymentModal.order?.total_harga || 0).toLocaleString() }}</h2>
            </div>

            <div class="payment-method">
              <label>Metode</label>
              <select v-model="paymentModal.method" class="method-select">
                <option value="Cash/Tunai">Cash / Tunai</option>
                <option value="QRIS">QRIS</option>
              </select>
            </div>

            <button 
              @click.stop="processPayment" 
              :disabled="processing"
              class="process-btn"
            >
              <ion-spinner v-if="processing"></ion-spinner>
              <span v-else>Bayar & Proses</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Order Detail Modal -->
      <div v-if="detailModal.isOpen" class="modal-overlay" @click.stop="closeDetailModal">
        <div class="modal-content detail-modal" @click.stop>
          <div class="modal-header">
            <h3>📋 Detail Pesanan #{{ detailModal.order?.id_transaksi }}</h3>
            <button @click="closeDetailModal" class="close-btn">×</button>
          </div>
          
          <div class="modal-body detail-body">
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
                  <span class="label">Waktu:</span>
                  <span class="value">{{ formatTime(detailModal.order?.tanggal_transaksi) }}</span>
                </div>
              </div>
            </div>

            <div class="detail-section">
              <h4>Items Pesanan</h4>
              <div class="items-detail">
                <div v-for="(item, idx) in parseOrderItems(detailModal.order?.items)" :key="idx" class="item-detail-row">
                  <span class="item-qty">{{ item.qty }}x</span>
                  <span class="item-name">{{ item.nama_menu }}</span>
                  <span class="item-price">Rp {{ (item.harga * item.qty).toLocaleString() }}</span>
                </div>
              </div>
            </div>

            <div v-if="detailModal.order?.catatan" class="detail-section">
              <h4>Catatan</h4>
              <p class="notes-text">{{ detailModal.order.catatan }}</p>
            </div>

            <div class="detail-section total-section-detail">
              <div class="total-row">
                <span class="total-label">Total Harga:</span>
                <span class="total-value">Rp {{ Number(detailModal.order?.total_harga || 0).toLocaleString() }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Clear Table Confirmation Modal -->
      <div v-if="clearTableModal.isOpen" class="modal-overlay" @click.stop="closeClearTableModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Kosongkan Meja?</h3>
            <button @click="closeClearTableModal" class="close-btn">×</button>
          </div>
          
          <div class="modal-body">
            <p>Apakah pelanggan di <strong>Meja {{ clearTableModal.table?.nomor_meja }}</strong> sudah pulang?</p>
            
            <button 
              @click.stop="confirmClearTable" 
              :disabled="processing"
              class="confirm-btn"
            >
              <ion-spinner v-if="processing"></ion-spinner>
              <span v-else>Ya, Kosongkan Meja</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Success Notification -->
      <div v-if="successNotification" class="notification">
        <span>{{ successNotification }}</span>
        <button @click="successNotification = ''" class="close-notif">Oke</button>
      </div>
    </div>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { IonPage, IonSpinner } from '@ionic/vue';
import authService from '@/services/auth';
import { tablesApi, ordersApi } from '@/services/restaurant';

const router = useRouter();
const user = authService.getUser();

// State
const pendingOrders = ref<any[]>([]);
const tables = ref<any[]>([]);
const processing = ref(false);
const successNotification = ref('');

const paymentModal = ref({
  isOpen: false,
  order: null as any,
  method: 'Cash/Tunai'
});

const clearTableModal = ref({
  isOpen: false,
  table: null as any
});

const detailModal = ref({
  isOpen: false,
  order: null as any
});

// Methods
const loadPendingOrders = async () => {
  try {
    const response = await ordersApi.getAll({ status: 'pending' });
    const result = response.data as any;
    if (result.success) {
      pendingOrders.value = result.data;
    }
  } catch (error) {
    console.error('Failed to load orders:', error);
  }
};

const loadTables = async () => {
  try {
    const response = await tablesApi.getAll();
    const result = response.data as any;
    if (result.success) {
      // Ensure we have 10 tables for display
      const allTables = result.data;
      // If less than 10, fill with dummy data
      while (allTables.length < 10) {
        allTables.push({
          id_meja: allTables.length + 1,
          nomor_meja: allTables.length + 1,
          status: 'kosong',
          kapasitas: 4
        });
      }
      tables.value = allTables.slice(0, 10);
    }
  } catch (error) {
    console.error('Failed to load tables:', error);
  }
};

const handleTableClick = (table: any) => {
  if (table.status === 'terisi') {
    // Open clear table confirmation
    clearTableModal.value = {
      isOpen: true,
      table: table
    };
  }
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

// Parse order items JSON
const parseOrderItems = (itemsString: string) => {
  if (!itemsString) return [];
  try {
    return typeof itemsString === 'string' ? JSON.parse(itemsString) : itemsString;
  } catch (error) {
    console.error('Failed to parse items:', error);
    return [];
  }
};

// Format time for display
const formatTime = (dateString: string) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const openPaymentModal = (order: any) => {
  paymentModal.value = {
    isOpen: true,
    order: order,
    method: 'Cash/Tunai'
  };
};

const closePaymentModal = () => {
  paymentModal.value = {
    isOpen: false,
    order: null,
    method: 'Cash/Tunai'
  };
};

const closeClearTableModal = () => {
  clearTableModal.value = {
    isOpen: false,
    table: null
  };
};

const processPayment = async () => {
  if (!paymentModal.value.order) return;
  
  processing.value = true;
  
  try {
    // Update order: set status to 'cooking' to send to kitchen
    // Store payment method for later
    const response = await ordersApi.update(paymentModal.value.order.id_transaksi, {
      action: 'update_status',
      status: 'cooking'
    });
    
    if (response.data.success) {
      successNotification.value = `Pembayaran dikonfirmasi! Order #${paymentModal.value.order.id_transaksi} dikirim ke Dapur (${paymentModal.value.method})`;
      
      // Reload data
      await Promise.all([loadPendingOrders(), loadTables()]);
      
      // Close modal
      closePaymentModal();
      
      // Auto-hide notification after 3 seconds
      setTimeout(() => {
        successNotification.value = '';
      }, 3000);
    }
  } catch (error) {
    console.error('Failed to process payment:', error);
    successNotification.value = 'Error: Gagal memproses pembayaran';
  } finally {
    processing.value = false;
  }
};

const confirmClearTable = async () => {
  if (!clearTableModal.value.table) return;
  
  processing.value = true;
  
  try {
    const response = await tablesApi.clear(clearTableModal.value.table.id_meja);
    
    if (response.data.success) {
      successNotification.value = `Meja ${clearTableModal.value.table.nomor_meja} berhasil dikosongkan!`;
      
      // Reload tables
      await loadTables();
      
      // Close modal
      closeClearTableModal();
      
      // Auto-hide notification
      setTimeout(() => {
        successNotification.value = '';
      }, 3000);
    }
  } catch (error) {
    console.error('Failed to clear table:', error);
    successNotification.value = 'Error: Gagal mengosongkan meja';
  } finally {
    processing.value = false;
  }
};

const handleLogout = () => {
  authService.logout();
  router.push('/login');
};

// Lifecycle
onMounted(() => {
  loadPendingOrders();
  loadTables();
  
  // Auto-refresh every 10 seconds
  setInterval(() => {
    loadPendingOrders();
    loadTables();
  }, 10000);
});
</script>

<style scoped>
.dashboard-container {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  min-height: 100vh;
  color: white;
  padding: 24px;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.header h1 {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
}

.logout-btn {
  padding: 10px 20px;
  background: transparent;
  border: 1px solid #dc2626;
  color: #dc2626;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: #dc2626;
  color: white;
}

.main-content {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 24px;
}

/* Orders Section */
.orders-section {
  background: #1e293b;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #334155;
}

.orders-section h2 {
  font-size: 18px;
  font-weight: 700;
  margin: 0 0 20px 0;
}

.empty-state {
  text-align: center;
  padding: 40px;
  color: #64748b;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th {
  text-align: left;
  padding: 12px;
  border-bottom: 2px solid #334155;
  color: #94a3b8;
  font-weight: 600;
  font-size: 13px;
  text-transform: uppercase;
}

.orders-table td {
  padding: 16px 12px;
  border-bottom: 1px solid #334155;
}

.order-id {
  background: #8b5cf6;
  padding: 4px 12px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 13px;
}

.table-badge {
  background: #334155;
  padding: 6px 12px;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13px;
}

.items-list {
  color: #94a3b8;
  font-size: 14px;
}

.total-price {
  color: #10b981;
  font-weight: 700;
  font-size: 16px;
}

.action-buttons {
  display: flex;
  gap: 8px;
  align-items: center;
}

.detail-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 18px;
  transition: all 0.2s;
}

.detail-btn:hover {
  background: #2563eb;
  transform: scale(1.05);
}

.pay-btn {
  background: #10b981;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  margin-left: 8px;
}

.pay-btn:hover {
  background: #059669;
}

.kitchen-btn {
  background: #f97316;
  color: white;
  border: none;
  padding: 8px 20px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.kitchen-btn:hover {
  background: #ea580c;
}

.kitchen-btn:disabled {
  background: #64748b;
  cursor: not-allowed;
  opacity: 0.6;
}

/* Tables Section */
.tables-section {
  background: #1e293b;
  border-radius: 12px;
  padding: 24px;
  border: 1px solid #334155;
}

.tables-section h2 {
  font-size: 18px;
  font-weight: 700;
  margin: 0 0 20px 0;
}

.tables-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
  margin-bottom: 16px;
}

.table-card {
  background: #334155;
  padding: 16px 8px;
  border-radius: 8px;
  text-align: center;
  transition: all 0.2s;
  cursor: pointer;
}

.table-card.kosong {
  background: #10b981;
}

.table-card.terisi {
  background: #dc2626;
}

.table-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.table-number {
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 4px;
}

.table-status {
  font-size: 10px;
  font-weight: 700;
  color: #e2e8f0;
}

.status-legend {
  text-align: center;
  color: #64748b;
  font-size: 12px;
  margin-top: 16px;
}

/* Payment Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-content {
  background: #1e293b;
  border-radius: 16px;
  width: 90%;
  max-width: 400px;
  border: 1px solid #334155;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #334155;
}

.modal-header h3 {
  font-size: 18px;
  font-weight: 700;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 28px;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.close-btn:hover {
  color: white;
}

.modal-body {
  padding: 24px;
}

.modal-body p {
  color: #94a3b8;
  font-size: 14px;
  margin-bottom: 20px;
  line-height: 1.6;
}

.total-section {
  text-align: center;
  margin-bottom: 24px;
}

.total-section span {
  color: #94a3b8;
  font-size: 14px;
}

.total-amount {
  font-size: 32px;
  font-weight: 700;
  color: white;
  margin: 8px 0;
}

.payment-method {
  margin-bottom: 24px;
}

.payment-method label {
  display: block;
  color: #94a3b8;
  font-size: 14px;
  margin-bottom: 8px;
  font-weight: 600;
}

.method-select {
  width: 100%;
  padding: 12px;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 8px;
  color: white;
  font-size: 14px;
  cursor: pointer;
}

.method-select option {
  background: #1e293b;
}

.process-btn,
.confirm-btn {
  width: 100%;
  padding: 14px;
  background: #8b5cf6;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.confirm-btn {
  background: #dc2626;
}

.process-btn:hover:not(:disabled) {
  background: #7c3aed;
}

.confirm-btn:hover:not(:disabled) {
  background: #b91c1c;
}

.process-btn:disabled,
.confirm-btn:disabled {
  background: #334155;
  cursor: not-allowed;
}

/* Notification */
.notification {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 12px;
  padding: 24px;
  z-index: 2000;
  text-align: center;
  min-width: 300px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.notification span {
  display: block;
  margin-bottom: 16px;
  font-size: 14px;
  color: white;
}

.close-notif {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 8px 24px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
}

@media (max-width: 1024px) {
  .main-content {
    grid-template-columns: 1fr;
  }
  
  .tables-grid {
    grid-template-columns: repeat(5, 1fr);
  }
}
</style>
