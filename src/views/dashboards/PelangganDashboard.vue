<template>
  <ion-page>
    <!-- Header with Location & Notification -->
    <ion-header class="ion-no-border">
      <ion-toolbar class="custom-toolbar">
        <div class="header-container">
          <div class="location-section">
            <ion-avatar class="user-avatar">
              <img src="https://api.iconify.design/mdi/chef-hat.svg?color=white" alt="User" />
            </ion-avatar>
            <div class="location-info">
              <ion-icon :icon="locationOutline" class="location-icon"></ion-icon>
              <span class="location-text">{{ user_name }}</span>
            </div>
          </div>
          <ion-button fill="clear" class="notification-btn">
            <ion-icon :icon="notificationsOutline" size="large"></ion-icon>
            <ion-badge v-if="cartItems.length > 0" color="danger" class="cart-badge">{{ cartItems.length }}</ion-badge>
          </ion-button>
        </div>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="main-content">
      <!-- Search Bar -->
      <div class="search-section">
        <div class="search-box">
          <ion-icon :icon="searchOutline" class="search-icon"></ion-icon>
          <input 
            type="text" 
            placeholder="Search menu..." 
            v-model="searchQuery"
            class="search-input"
          />
        </div>
      </div>

      <!-- Categories -->
      <div class="categories-section">
        <h2 class="section-title">Categories</h2>
        <div class="category-chips">
          <ion-chip 
            v-for="cat in categories" 
            :key="cat"
            :class="{ active: selectedCategory === cat }"
            @click="selectedCategory = cat"
          >
            {{ cat }}
          </ion-chip>
        </div>
      </div>

      <!-- Menu Grid -->
      <div class="menu-grid">
        <div 
          v-for="item in displayedMenu" 
          :key="item.id_menu"
          class="menu-card"
          @click="openDetail(item)"
        >
          <div class="card-image">
            <img 
              :src="item.foto_makanan || 'https://via.placeholder.com/300x200/D4A574/fff?text=Menu'" 
              :alt="item.nama_menu" 
            />
          </div>
          <div class="card-content">
            <h3 class="menu-name">{{ item.nama_menu }}</h3>
            <div class="menu-meta">
              <div class="rating">
                <ion-icon :icon="star" class="star-icon"></ion-icon>
                <span>4.5</span>
              </div>
              <div class="volume">Volume {{ item.porsi || '1' }} porsi</div>
            </div>
            <div class="card-bottom">
              <div class="price">Rp {{ formatPrice(item.harga) }}</div>
              <button class="btn-add">
                <ion-icon :icon="addOutline"></ion-icon>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="displayedMenu.length === 0" class="empty-state">
        <ion-icon :icon="fastFoodOutline" size="large"></ion-icon>
        <p>No menu items found</p>
      </div>

      <!-- Spacing for bottom nav -->
      <div style="height: 80px;"></div>
    </ion-content>

    <!-- Product Detail Modal -->
    <ion-modal 
      :is-open="detailModal.isOpen"
      @didDismiss="closeDetail"
      :initial-breakpoint="0.85"
      :breakpoints="[0, 0.5, 0.85]"
      class="product-modal"
    >
      <div class="modal-content" v-if="detailModal.item">
        <!-- Modal Header with Image -->
        <div class="modal-header">
          <button @click="closeDetail" class="btn-close">
            <ion-icon :icon="closeOutline"></ion-icon>
          </button>
          <button @click.stop="toggleFavorite(detailModal.item)" class="btn-favorite-modal">
            <ion-icon :icon="isFavorite(detailModal.item) ? heart : heartOutline"></ion-icon>
          </button>
          
          <div class="product-image">
            <img :src="getImageUrl(detailModal.item)" :alt="detailModal.item.nama_menu">
          </div>
        </div>

        <!-- Modal Body - Compact -->
        <div class="modal-body">
          <!-- Product Info - Compact -->
          <div class="product-info-compact">
            <div>
              <h2 class="product-title">{{ detailModal.item.nama_menu }}</h2>
              <p class="product-desc">{{ detailModal.item.deskripsi || 'Delicious menu item' }}</p>
            </div>
            <div class="product-price-large">Rp {{ formatPrice(detailModal.item.harga) }}</div>
          </div>

          <!-- Order Type - Horizontal Pills -->
          <div class="order-type-compact">
            <button 
              :class="['order-pill', { active: orderType === 'takeaway' }]"
              @click="orderType = 'takeaway'; selectedTable = null"
            >
              <ion-icon :icon="bagHandleOutline"></ion-icon>
              Dibungkus
            </button>
            
            <button 
              :class="['order-pill', { active: orderType === 'dine-in' }]"
              @click="orderType = 'dine-in'"
            >
              <ion-icon :icon="restaurantOutline"></ion-icon>
              Dine In
            </button>
          </div>

          <!-- Table Selection - Compact Grid (only if dine-in) -->
          <div v-if="orderType === 'dine-in'" class="table-selection-compact">
            <label class="selection-label">Pilih Meja</label>
            <div class="table-grid-compact">
              <button
                v-for="table in availableTables"
                :key="table.id_meja"
                :class="['table-chip', { active: selectedTable?.id_meja === table.id_meja }]"
                @click="selectedTable = table"
              >
                {{ table.nomor_meja }}
              </button>
            </div>
          </div>

          <!-- Notes/Special Request -->
          <div class="notes-section">
            <label class="selection-label">Catatan (Opsional)</label>
            <textarea 
              v-model="orderNotes"
              placeholder="Contoh: Tidak pedas, tanpa MSG, extra sambal..."
              class="notes-input"
              rows="2"
              maxlength="200"
            ></textarea>
            <span class="char-count">{{ orderNotes.length }}/200</span>
          </div>
        </div>

        <!-- Sticky Bottom Action Bar -->
        <div class="sticky-action-bar">
          <div class="quantity-selector">
            <button @click="decreaseQty" class="qty-btn-compact">
              <ion-icon :icon="removeOutline"></ion-icon>
            </button>
            <span class="qty-display">{{ quantity }}</span>
            <button @click="increaseQty" class="qty-btn-compact">
              <ion-icon :icon="addOutline"></ion-icon>
            </button>
          </div>
          
          <button class="add-to-cart-btn" @click="addToCart">
            <ion-icon :icon="bagOutline"></ion-icon>
            Add • Rp {{ formatPrice(detailModal.item.harga * quantity) }}
          </button>
        </div>
      </div>
    </ion-modal>

    <!-- Shopping Cart Modal -->
    <ion-modal
      :is-open="cartModal.isOpen"
      @didDismiss="cartModal.isOpen = false"
      :initial-breakpoint="0.75"
      :breakpoints="[0, 0.5, 0.75]"
      class="cart-modal"
    >
      <div class="cart-content">
        <!-- Cart Header -->
        <div class="cart-header">
          <div>
            <h2 class="cart-title">Keranjang Belanja</h2>
            <span class="cart-subtitle">{{ cartItems.length }} item</span>
          </div>
          <button @click="cartModal.isOpen = false" class="btn-close-cart">
            <ion-icon :icon="closeOutline"></ion-icon>
          </button>
        </div>

        <!-- Cart Body -->
        <div class="cart-body">
          <!-- Empty State -->
          <div v-if="cartItems.length === 0" class="empty-cart">
            <ion-icon :icon="cartOutline"></ion-icon>
            <p>Keranjang masih kosong</p>
            <button @click="cartModal.isOpen = false" class="btn-start-shopping">
              Mulai Belanja
            </button>
          </div>

          <!-- Cart Items -->
          <div v-else class="cart-items-list">
            <div v-for="(item, index) in cartItems" :key="index" class="cart-item-card">
              <!-- Item Image & Info -->
              <div class="cart-item-main">
                <div class="cart-item-image">
                  <img :src="getImageUrl(item)" :alt="item.nama_menu">
                </div>
                
                <div class="cart-item-info">
                  <h3 class="cart-item-name">{{ item.nama_menu }}</h3>
                  <div class="cart-item-meta">
                    <ion-icon :icon="item.orderType === 'takeaway' ? bagHandleOutline : restaurantOutline"></ion-icon>
                    <span v-if="item.orderType === 'takeaway'">Dibungkus</span>
                    <span v-else>Dine In • {{ item.tableNumber }}</span>
                  </div>
                  <p v-if="item.notes" class="cart-item-notes">
                    <ion-icon :icon="chatboxOutline"></ion-icon>
                    {{ item.notes }}
                  </p>
                  <div class="cart-item-price">Rp {{ formatPrice(item.harga) }}</div>
                </div>
              </div>

              <!-- Quantity & Actions -->
              <div class="cart-item-actions">
                <div class="cart-qty-controls">
                  <button @click="updateCartQty(index, -1)" class="cart-qty-btn">
                    <ion-icon :icon="removeOutline"></ion-icon>
                  </button>
                  <span class="cart-qty">{{ item.qty }}</span>
                  <button @click="updateCartQty(index, 1)" class="cart-qty-btn">
                    <ion-icon :icon="addOutline"></ion-icon>
                  </button>
                </div>
                
                <div class="cart-item-subtotal">
                  Rp {{ formatPrice(item.harga * item.qty) }}
                </div>
                
                <button @click="removeFromCart(index)" class="btn-remove-item">
                  <ion-icon :icon="trashOutline"></ion-icon>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Footer (Sticky) -->
        <div v-if="cartItems.length > 0" class="cart-footer">
          <div class="total-section">
            <span class="total-label">Total</span>
            <span class="total-price">Rp {{ formatPrice(totalPrice) }}</span>
          </div>
          <button @click="placeOrder" class="btn-place-order">
            <ion-icon :icon="checkmarkCircle"></ion-icon>
            Place Order
          </button>
        </div>
      </div>
    </ion-modal>

    <!-- Bottom Navigation -->
    <ion-footer class="bottom-nav">
      <div class="nav-container">
        <!-- Animated Blob Background -->
        <div class="nav-blob" :style="blobPosition"></div>
        
        <button 
          ref="homeBtn"
          class="nav-btn" 
          :class="{ active: currentTab === 'home' }" 
          @click="switchTab('home')"
        >
          <ion-icon :icon="currentTab === 'home' ? homeSharp : homeOutline"></ion-icon>
        </button>
        <button 
          ref="favBtn"
          class="nav-btn" 
          :class="{ active: currentTab === 'favorites' }" 
          @click="showFavorites"
        >
          <ion-icon :icon="currentTab === 'favorites' ? heartSharp : heartOutline"></ion-icon>
          <ion-badge v-if="favoriteItems.length > 0" color="danger" class="nav-badge">{{ favoriteItems.length }}</ion-badge>
        </button>
        <button 
          ref="cartBtn"
          class="nav-btn" 
          :class="{ active: currentTab === 'cart' }" 
          @click="switchTab('cart'); cartModal.isOpen = true"
        >
          <ion-icon :icon="bagOutline"></ion-icon>
          <ion-badge v-if="cartItems.length > 0" color="danger" class="nav-badge">{{ cartItems.length }}</ion-badge>
        </button>
        <button 
          ref="logoutBtn"
          class="nav-btn" 
          @click="handleLogout"
        >
          <ion-icon :icon="logOutOutline"></ion-icon>
        </button>
      </div>
    </ion-footer>

    <!-- Toast Notification -->
    <ion-toast
      :is-open="toast.show"
      :message="toast.message"
      :duration="2000"
      :color="toast.color"
      position="top"
      class="custom-toast"
      @didDismiss="toast.show = false"
    ></ion-toast>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
  IonPage, IonHeader, IonToolbar, IonContent, IonFooter,
  IonButton, IonIcon, IonAvatar, IonBadge, IonChip,
  IonModal, IonToast
} from '@ionic/vue';
import { 
  heartOutline,
  heartSharp,
  heart,
  searchOutline,
  bagOutline, 
  addOutline, 
  removeOutline, 
  closeOutline,
  homeOutline,
  homeSharp,
  cartOutline,
  logOutOutline,
  notificationsOutline,
  locationOutline,
  bagHandleOutline,
  restaurantOutline,
  peopleOutline,
  star,
  arrowBackOutline,
  fastFoodOutline,
  trashOutline,
  chatboxOutline, 
  checkmarkCircle
} from 'ionicons/icons';
import { useRouter } from 'vue-router';
import { menuApi, tablesApi, ordersApi } from '@/services/restaurant';
import authService from '@/services/auth';

const router = useRouter();

// State
const currentUser = ref(authService.getCurrentUser());
const restaurant_name = ref('Resto App');
const user_name = ref(currentUser.value?.username || 'Guest');
const searchQuery = ref('');
const selectedCategory = ref('all');
const categories = ref(['all', 'makanan', 'minuman', 'snack']); // Lowercase to match database
const menuItems = ref<any[]>([]);
const currentTab = ref('home');

// Nav button refs for blob positioning
const homeBtn = ref<HTMLElement | null>(null);
const favBtn = ref<HTMLElement | null>(null);
const cartBtn = ref<HTMLElement | null>(null);
const logoutBtn = ref<HTMLElement | null>(null);
const favoriteItems = ref<any[]>([]);
const cartItems = ref<any[]>([]);

const detailModal = ref({
  isOpen: false,
  item: null as any
});

// Order type and table selection
const orderType = ref('takeaway'); // 'takeaway' or 'dine-in'
const selectedTable = ref<any>(null);
const availableTables = ref<any[]>([]);
const orderNotes = ref(''); // Customer notes/special requests
const quantity = ref(1);

const cartModal = ref({
  isOpen: false
});

const toast = ref({
  show: false,
  message: '',
  color: 'success'
});

// Load menus on component mount
onMounted(async () => {
  // Load favorites from localStorage
  const savedFavorites = localStorage.getItem('favoriteItems');
  if (savedFavorites) {
    try {
      favoriteItems.value = JSON.parse(savedFavorites);
    } catch (e) {
      console.error('Failed to parse favorites:', e);
    }
  }
  
  await loadMenu();
  
  // Force blob position calculation after DOM is ready
  setTimeout(() => {
    // Trigger reactivity by re-setting currentTab
    const temp = currentTab.value;
    currentTab.value = '';
    setTimeout(() => {
      currentTab.value = temp;
    }, 50);
  }, 100);
});

// Load menu
const loadMenu = async () => {
  try {
    const response = await menuApi.getAll();
    const data = Array.isArray(response.data) ? response.data : (response.data as any)?.data || [];
    menuItems.value = data;
  } catch (error) {
    console.error('Failed to load menu:', error);
  }
};

// Load available tables
const loadAvailableTables = async () => {
  try {
    const response = await tablesApi.getAll();
    const data = Array.isArray(response.data) ? response.data : (response.data as any)?.data || [];
    // Filter only available tables (status = 'kosong') and sort numerically
    const filtered = data.filter((table: any) => table.status === 'kosong');
    
    // Sort tables numerically by extracting number from nomor_meja
    filtered.sort((a: any, b: any) => {
      const numA = parseInt(a.nomor_meja.replace(/\D/g, '')) || 0;
      const numB = parseInt(b.nomor_meja.replace(/\D/g, '')) || 0;
      return numA - numB;
    });
    
    availableTables.value = filtered;
  } catch (error) {
    console.error('Failed to load tables:', error);
    availableTables.value = [];
  }
};

// Cart management functions
const updateCartQty = (index: number, change: number) => {
  const item = cartItems.value[index];
  const newQty = item.qty + change;
  
  if (newQty <= 0) {
    removeFromCart(index);
  } else {
    item.qty = newQty;
  }
};

const removeFromCart = (index: number) => {
  const item = cartItems.value[index];
  cartItems.value.splice(index, 1);
  showToast(`${item.nama_menu} dihapus dari keranjang`, 'warning');
};

const placeOrder = async () => {
  if (cartItems.value.length === 0) {
    showToast('Keranjang masih kosong', 'warning');
    return;
  }

  if (!currentUser.value?.id_user) {
    showToast('Silakan login terlebih dahulu', 'warning');
    return;
  }
  
  // Get table from the first item
  const firstItem = cartItems.value[0];
  // const tableData = firstItem.selected_table; // Removed as it was causing issues
  
  // Check if order type is takeaway
  const isTakeaway = firstItem.orderType === 'takeaway';
  
  let idMeja = firstItem.id_meja;
  
  if (!idMeja) {
    if (isTakeaway) {
      // If takeaway, use a default table ID (e.g., 1) or a specific "Takeaway" table if exists
      // For now, we use 1 to bypass the constraint, but the order type in items will indicate it's takeaway
      idMeja = 1; 
    } else {
      showToast('Mohon pilih meja untuk pesanan ini', 'warning');
      return;
    }
  }

  try {
    const orderData = {
      id_meja: idMeja,
      id_user: currentUser.value.id_user,
      nama_pelanggan: currentUser.value.username || 'Pelanggan',
      items: cartItems.value.map(item => {
        const catatan = `${item.orderType === 'takeaway' ? '(DIBUNGKUS)' : ''} ${item.notes || ''}`.trim();
        return {
          id_menu: item.id_menu,
          jumlah: item.qty,
          ...(catatan && { catatan })
        };
      })
    };
    
    console.log('Sending order data:', orderData);
    
    // Call API
    const response = await ordersApi.create(orderData);
    
    console.log('Order response:', response);
    
    // Backend returns success in response.data
    if (response.data?.success) {
      const itemCount = cartItems.value.length;
      cartItems.value = [];
      cartModal.value.isOpen = false;
      showToast(`Pesanan (${itemCount} item) berhasil dikirim ke kasir!`, 'success');
    } else {
      showToast(response.data?.message || 'Gagal mengirim pesanan', 'danger');
    }
  } catch (error: any) {
    console.error('Place order error:', error);
    console.error('Error response:', error.response?.data);
    const errorMsg = error.response?.data?.message || error.message || 'Gagal mengirim pesanan';
    showToast(`Error: ${errorMsg}`, 'danger');
  }
};

// Filtered menu
const displayedMenu = computed(() => {
  let filtered = menuItems.value;
  
  // If on favorites tab, only show favorites
  if (currentTab.value === 'favorites') {
    const favoriteIds = favoriteItems.value.map(fav => fav.id_menu);
    filtered = filtered.filter(item => favoriteIds.includes(item.id_menu));
    return filtered; // Return early, no other filters needed
  }
  
  // Filter by category
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(item => {
      // API returns nama_kategori field
      const itemCategory = (item.nama_kategori || item.kategori || '').toLowerCase().trim();
      const selectedCat = selectedCategory.value.toLowerCase().trim();
      return itemCategory === selectedCat;
    });
  }

  // Filter by search
  if (searchQuery.value) {
    filtered = filtered.filter(item =>
      item.nama_menu?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }
  
  return filtered;
});

// Detail modal
const openDetail = async (item: any) => {
  detailModal.value.item = item;
  detailModal.value.isOpen = true;
  quantity.value = 1;
  orderType.value = 'takeaway';
  selectedTable.value = null;
  orderNotes.value = ''; // Reset notes
  // Load available tables
  await loadAvailableTables();
};

const closeDetail = () => {
  detailModal.value.isOpen = false;
  setTimeout(() => {
    detailModal.value.item = null;
  }, 300);
};

const toggleFavorite = (item: any) => {
  const index = favoriteItems.value.findIndex(fav => fav.id_menu === item.id_menu);
  
  if (index >= 0) {
    favoriteItems.value.splice(index, 1);
    showToast('Removed from favorites', 'warning');
  } else {
    favoriteItems.value.push(item);
    showToast('Added to favorites!', 'success');
  }
  
  // Save to localStorage
  localStorage.setItem('favoriteItems', JSON.stringify(favoriteItems.value));
};

const isFavorite = (item: any) => {
  return favoriteItems.value.some(fav => fav.id_menu === item.id_menu);
};

// Cart total price
const totalPrice = computed(() => {
  return cartItems.value.reduce((total, item) => {
    return total + (item.harga * item.qty);
  }, 0);
});

// Quantity
const increaseQty = () => {
  quantity.value++;
};

const decreaseQty = () => {
  if (quantity.value > 1) quantity.value--;
};

// Add to cart
const addToCart = () => {
  // Validate dine-in requires table selection
  if (orderType.value === 'dine-in' && !selectedTable.value) {
    showToast('Silakan pilih meja terlebih dahulu', 'warning');
    return;
  }
  
  const newItem = {
    ...detailModal.value.item,
    orderType: orderType.value,
    tableNumber: selectedTable.value?.nomor_meja || null,
    id_meja: selectedTable.value?.id_meja || null,
    notes: orderNotes.value.trim(), // Save customer notes
    qty: quantity.value
  };
  
  // Check if item with same order type, table, and notes already exists
  const existingIndex = cartItems.value.findIndex(
    item => item.id_menu === newItem.id_menu && 
            item.orderType === newItem.orderType && 
            item.id_meja === newItem.id_meja &&
            item.notes === newItem.notes
  );
  
  if (existingIndex >= 0) {
    cartItems.value[existingIndex].qty += quantity.value;
  } else {
    cartItems.value.push(newItem);
  }
  
  const orderTypeText = orderType.value === 'takeaway' ? 'Dibungkus' : `Meja ${selectedTable.value?.nomor_meja}`;
  showToast(`Added ${quantity.value}x ${detailModal.value.item.nama_menu} (${orderTypeText})!`, 'success');
  closeDetail();
};

const openCart = () => {
  currentTab.value = 'cart';
  cartModal.value.isOpen = true;
};

const closeCart = () => {
  cartModal.value.isOpen = false;
  currentTab.value = 'home';
};

// Blob position computed property
const blobPosition = computed(() => {
  const buttons: Record<string, HTMLElement | null> = {
    home: homeBtn.value,
    favorites: favBtn.value,
    cart: cartBtn.value
  };
  
  const activeBtn = buttons[currentTab.value];
  if (!activeBtn) return { transform: 'translateX(0) translateY(-50%)', opacity: '0' };
  
  const container = activeBtn.parentElement;
  if (!container) return { transform: 'translateX(0) translateY(-50%)', opacity: '0' };
  
  const btnRect = activeBtn.getBoundingClientRect();
  const containerRect = container.getBoundingClientRect();
  
  // If no valid position (page just loaded), return 0,0 but still show blob for valid tabs
  if (btnRect.width === 0 || containerRect.width === 0) {
    return { transform: 'translateX(0) translateY(-50%)', opacity: currentTab.value ? '1' : '0' };
  }
  
  const offsetX = btnRect.left - containerRect.left + (btnRect.width / 2) - 32.5; // 32.5 = half of 65px width
  
  return {
    transform: `translateX(${offsetX}px) translateY(-50%)`,
    opacity: '1'
  };
});

// Switch tab with animation
const switchTab = (tab: string) => {
  currentTab.value = tab;
};

const showFavorites = () => {
  currentTab.value = 'favorites';
  if (favoriteItems.value.length === 0) {
    showToast('No favorites yet!', 'warning');
  } else {
    showToast(`You have ${favoriteItems.value.length} favorites!`, 'success');
  }
};

// Helpers
const getImageUrl = (item: any) => {
  return item.foto_makanan ||  `https://ui-avatars.com/api/?name=${encodeURIComponent(item.nama_menu)}&background=8B5CF6&color=fff&size=400`;
};

const formatPrice = (price: number) => {
  return price?.toLocaleString('id-ID') || '0';
};

const showToast = (message: string, color: 'success' | 'warning' | 'danger' = 'success') => {
  toast.value = { show: true, message, color };
};

const handleLogout = () => {
  authService.logout();
  router.push('/login');
};

// Lifecycle
onMounted(() => {
  loadMenu();
});
</script>

<style scoped>
/* Mobile-First Coffee Shop Design */
ion-header.ion-no-border::after {
  display: none;
}

.custom-toolbar {
  --background: #1a1a2e;
  --min-height: 70px;
  --padding-top: 10px;
  --padding-bottom: 10px;
}

.header-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.25rem;
}

.location-section {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-avatar img {
  width: 28px;
  height: 28px;
  object-fit: contain;
}

.location-info {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.location-icon {
  color: #8B5CF6;
  font-size: 1.1rem;
}

.location-text {
  font-weight: 600;
  color: white;
  font-size: 0.95rem;
}

.notification-btn {
  --color: white;
  position: relative;
}

.cart-badge {
  position: absolute;
  top: 8px;
  right: 8px;
  font-size: 0.65rem;
  min-width: 16px;
  height: 16px;
}

/* Content */
.main-content {
  --background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
}

/* Search */
.search-section {
  padding: 1rem 1.25rem;
}

.search-box {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.search-icon {
  color: #8B5CF6;
  font-size: 1.4rem;
}

.search-input {
  flex: 1;
  border: none;
  outline: none;
  font-size: 0.95rem;
  color: white;
  background: transparent;
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

/* Categories */
.categories-section {
  padding: 0 1.25rem 1rem;
}

.section-title {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: white;
}

.category-chips {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
  -webkit-overflow-scrolling: touch;
}

.category-chips::-webkit-scrollbar {
  display: none;
}

ion-chip {
  --background: rgba(255, 255, 255, 0.1);
  --color: rgba(255, 255, 255, 0.6);
  margin: 0;
  font-weight: 600;
  font-size: 0.9rem;
  padding: 0.625rem 1.25rem;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid rgba(255, 255, 255, 0.15);
  text-transform: capitalize;
}

ion-chip.active {
  --background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
  --color: white;
  border-color: #8B5CF6;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
  transform: translateY(-2px);
}

ion-chip:hover:not(.active) {
  --background: rgba(255, 255, 255, 0.15);
  --color: rgba(255, 255, 255, 0.8);
  border-color: rgba(255, 255, 255, 0.3);
}

/* Menu Grid */
.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 1.25rem;
  padding: 0 1.25rem 2rem;
}

.menu-card {
  background: linear-gradient(135deg, #2d2d44 0%, #1f1f2e 100%);
  border-radius: 16px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.menu-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(139, 92, 246, 0.3);
  border-color: rgba(139, 92, 246, 0.5);
}

.menu-card:active {
  transform: scale(0.98);
}

.card-image {
  width: 100%;
  height: 140px;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-content {
  padding: 1rem;
}

.menu-name {
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
  font-weight: 700;
  color: white;
}

.menu-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.7);
}

.rating {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: #D4A574;
  font-weight: 600;
}

.star-icon {
  font-size: 0.9rem;
}

.card-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.price {
  font-size: 1.1rem;
  font-weight: 700;
  color: white;
}

.btn-add {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: white;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add ion-icon {
  font-size: 1.2rem;
  color: #5A4A3A;
}

/* Detail Modal */
.detail-modal {
}

/* ==================== PRODUCT MODAL - COMPACT MOBILE DESIGN ==================== */
.product-modal {
  --background: transparent;
}

.product-modal .modal-content {
  background: linear-gradient(to bottom, #1a1a2e 0%, #16213e 100%);
  border-radius: 24px 24px 0 0;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Modal Header with Image */
.modal-header {
  position: relative;
  height: 240px;
  background: #0f0f1e;
  flex-shrink: 0;
}

.btn-close {
  position: absolute;
  top: 1rem;
  left: 1rem;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(10px);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-favorite-modal {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(10px);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-close ion-icon,
.btn-favorite-modal ion-icon {
  font-size: 1.4rem;
}

.btn-close:active,
.btn-favorite-modal:active {
  transform: scale(0.9);
}

.btn-close:active,
.btn-favorite-modal:active {
  transform: scale(0.9);
}

.btn-close ion-icon,
.btn-favorite-modal ion-icon {
  font-size: 1.4rem;
}

.btn-favorite-modal:active ion-icon {
  color: #ff4757;
}

.product-image {
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Modal Body - Scrollable */
.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem;
  padding-bottom: 7rem; /* Increased: Space for sticky bar + extra */
  -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
}

/* Product Info - Compact */
.product-info-compact {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.product-title {
  font-size: 1.3rem;
  font-weight: 700;
  color: white;
  margin: 0 0 0.5rem 0;
  line-height: 1.3;
}

.product-desc {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
  margin: 0;
  line-height: 1.5;
}

.product-price-large {
  font-size: 1.4rem;
  font-weight: 700;
  color: #8B5CF6;
  white-space: nowrap;
  flex-shrink: 0;
}

/* Order Type - Horizontal Pills */
.order-type-compact {
  display: flex;
  gap: 0.625rem;
  margin-bottom: 1.25rem;
}

.order-pill {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.6);
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.order-pill ion-icon {
  font-size: 1.2rem;
}

.order-pill.active {
  background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
  border-color: #8B5CF6;
  color: white;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.order-pill:active {
  transform: scale(0.97);
}

/* Table Selection - Compact */
.table-selection-compact {
  animation: slideDown 0.3s ease;
}

.selection-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 0.75rem;
}

.table-grid-compact {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.table-chip {
  padding: 0.625rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 60px;
  text-align: center;
}

.table-chip.active {
  background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
  border-color: #8B5CF6;
  color: white;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
  transform: translateY(-2px);
}

.table-chip:active {
  transform: scale(0.95);
}

/* Notes Section */
.notes-section {
  margin-top: 1.25rem;
}

.notes-input {
  width: 100%;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 2px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  color: white;
  font-size: 0.9rem;
  font-family: inherit;
  resize: vertical;
  transition: all 0.3s ease;
}

.notes-input::placeholder {
  color: rgba(255, 255, 255, 0.4);
}

.notes-input:focus {
  outline: none;
  border-color: #8B5CF6;
  background: rgba(255, 255, 255, 0.08);
}

.char-count {
  display: block;
  text-align: right;
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.5);
  margin-top: 0.25rem;
}

/* Sticky Bottom Action Bar */
.sticky-action-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, #16213e 0%, rgba(22, 33, 62, 0.98) 100%);
  backdrop-filter: blur(10px);
  padding: 1rem 1.25rem;
  padding-bottom: max(1rem, env(safe-area-inset-bottom)); /* iOS safe area */
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  gap: 0.75rem;
  align-items: center;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.3);
  z-index: 50; /* Reasonable value for modal context */
}

.product-modal .sticky-action-bar {
  position: sticky;
  bottom: 0;
  margin: 0 -1.25rem;
  padding-bottom: 5rem; /* Increased to clear bottom nav */
}

.quantity-selector {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 0.5rem 0.75rem;
}

.qty-btn-compact {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(139, 92, 246, 0.2);
  border: 1px solid #8B5CF6;
  color: #8B5CF6;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.qty-btn-compact:active {
  background: #8B5CF6;
  color: white;
  transform: scale(0.9);
}

.qty-btn-compact ion-icon {
  font-size: 1.1rem;
}

.qty-display {
  min-width: 32px;
  text-align: center;
  font-weight: 700;
  font-size: 1rem;
  color: white;
}

.add-to-cart-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.625rem;
  padding: 0.875rem 1.25rem;
  background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
}

.add-to-cart-btn:active {
  transform: scale(0.97);
}

.add-to-cart-btn ion-icon {
  font-size: 1.2rem;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Bottom Nav */
.bottom-nav {
  background: white;
  box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.1);
}

/* ==================== SHOPPING CART MODAL==================== */
.cart-modal {
  --background: transparent;
}

.cart-content {
  background: linear-gradient(to bottom, #1a1a2e 0%, #16213e 100%);
  border-radius: 24px 24px 0 0;
  max-height: 75vh; /* Limited height */
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

/* Cart Header */
.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
}

.cart-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  margin: 0 0 0.25rem 0;
}

.cart-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.6);
}

.btn-close-cart {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-close-cart:active {
  transform: scale(0.9);
  background: rgba(255, 255, 255, 0.15);
}

/* Cart Body */
.cart-body {
  flex: 1;
  overflow-y: auto;
  padding-bottom: 0; /* No padding needed - footer is sticky now */
  -webkit-overflow-scrolling: touch;
}

/* Empty State */
.empty-cart {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.empty-cart ion-icon {
  font-size: 5rem;
  color: rgba(255, 255, 255, 0.3);
  margin-bottom: 1rem;
}

.empty-cart p {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 1.5rem;
}

.btn-start-shopping {
  padding: 0.875rem 2rem;
  background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
  border: none;
  border-radius: 12px;
  color: white;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-start-shopping:active {
  transform: scale(0.97);
}

/* Cart Items List */
.cart-items-list {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-item-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.cart-item-main {
  display: flex;
  gap: 1rem;
}

.cart-item-image {
  width: 80px;
  height: 80px;
  border-radius: 12px;
  overflow: hidden;
  flex-shrink: 0;
  background: rgba(255, 255, 255, 0.1);
}

.cart-item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cart-item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.cart-item-name {
  font-size: 1rem;
  font-weight: 700;
  color: white;
  margin: 0;
}

.cart-item-meta {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.6);
}

.cart-item-meta ion-icon {
  font-size: 1rem;
}

.cart-item-notes {
  display: flex;
  align-items: flex-start;
  gap: 0.375rem;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  font-style: italic;
  margin: 0;
  padding: 0.5rem;
  background: rgba(139, 92, 246, 0.1);
  border-left: 2px solid #8B5CF6;
  border-radius: 6px;
}

.cart-item-notes ion-icon {
  font-size: 0.9rem;
  flex-shrink: 0;
  margin-top: 0.125rem;
}

.cart-item-price {
  font-size: 0.9rem;
  font-weight: 700;
  color: #8B5CF6;
}

/* Cart Item Actions */
.cart-item-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.cart-qty-controls {
  display: flex;
  align-items: center;
 gap: 0.75rem;
}

.cart-qty-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(139, 92, 246, 0.2);
  border: 1px solid #8B5CF6;
  color: #8B5CF6;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cart-qty-btn:active {
  background: #8B5CF6;
  color: white;
  transform: scale(0.9);
}

.cart-qty {
  min-width: 32px;
  text-align: center;
  font-weight: 700;
  font-size: 1rem;
  color: white;
}

.cart-item-subtotal {
  font-size: 1.1rem;
  font-weight: 700;
  color: white;
  flex: 1;
  text-align: center;
}

.btn-remove-item {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: rgba(255, 77, 87, 0.2);
  border: 1px solid rgba(255, 77, 87, 0.5);
  color: #ff4d57;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-remove-item:active {
  background: rgba(255, 77, 87, 0.3);
  transform: scale(0.9);
}

/* Cart Footer */
.cart-footer {
  position: sticky; /* Sticky within modal, not fixed to viewport */
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(to top, #16213e 0%, rgba(22, 33, 62, 0.98) 100%);
  backdrop-filter: blur(10px);
  padding: 0.5rem 0.875rem;
  padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.3);
  z-index: 100;
  margin-top: auto; /* Push to bottom of flex container */
  flex-shrink: 0; /* Don't shrink when content is small */
}

.total-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem; /* Minimal margin */
}

.total-label {
  font-size: 0.8rem; /* Smaller */
  color: rgba(255, 255, 255, 0.7);
  font-weight: 600;
}

.total-price {
  font-size: 1.1rem; /* Smaller */
  font-weight: 700;
  color: #8B5CF6;
}

.btn-place-order {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem; /* Minimal gap */
  padding: 0.625rem 0.875rem; /* Compact padding */
  background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
  border: none;
  border-radius: 10px; /* Slightly smaller radius */
  color: white;
  font-weight: 700;
  font-size: 0.95rem; /* Smaller font */
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
}

.btn-place-order:active {
  transform: scale(0.98);
}

.btn-place-order ion-icon {
  font-size: 1.1rem; /* Smaller icon */
}

.nav-container {
  display: flex;
  justify-content: space-around;
  padding: 0.75rem 0;
  position: relative; /* For blob positioning */
}

/* Animated Blob Background */
.nav-blob {
  position: absolute;
  top: 50%;
  left: 0;
  width: 65px; /* Wider to wrap button */
  height: 50px; /* Taller rounded rectangle */
  background: linear-gradient(135deg, #10B981 0%, #8B5CF6 100%); /* Green to purple */
  border-radius: 25px; /* Rounded pill shape */
  transform: translateY(-50%) translateX(0);
  transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55), 
              width 0.4s ease,
              border-radius 0.4s ease,
              opacity 0.3s ease;
  opacity: 0;
  z-index: 0;
  filter: none; /* No blur - solid */
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4); /* Green glow */
}

.nav-btn {
  background: transparent;
  border: none;
  padding: 0.5rem 1.25rem;
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
  z-index: 1; /* Above blob */
}

.nav-btn ion-icon {
  font-size: 1.6rem;
  color: #999;
  transition: all 0.3s ease;
}

.nav-btn.active ion-icon {
  color: white; /* White when active (on blob) */
  transform: scale(1.1);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.nav-badge {
  position: absolute;
  top: 0;
  right: 0.5rem;
  font-size: 0.65rem;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 3rem 2rem;
  color: #999;
}

.empty-state ion-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  opacity: 0.5;
}

/* Tablet */
@media (min-width: 768px) {
  .menu-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

/* Desktop */
@media (min-width: 1024px) {
  .menu-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Custom Toast Styling */
.custom-toast {
  --background: rgba(26, 26, 46, 0.95);
  --color: white;
  --border-radius: 12px;
  --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  --min-height: 50px;
  --max-width: 90%;
  margin-top: 10px;
}

.custom-toast::part(message) {
  font-size: 0.9rem;
  font-weight: 500;
  padding: 0.5rem 1rem;
}

/* Color variants */
.custom-toast.toast-success {
  --background: linear-gradient(135deg, #10B981 0%, #059669 100%);
}

.custom-toast.toast-warning {
  --background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
}

.custom-toast.toast-danger {
  --background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
}
</style>
