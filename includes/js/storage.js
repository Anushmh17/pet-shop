/**
 * Pet Shop Storage Manager
 * Switched from localStorage to MySQL Backend (via PHP API)
 */

const DB = {
  // --- BASE FETCH WRAPPER ---
  async callAPI(action, method = 'GET', data = null) {
    try {
      const options = {
        method: method,
        headers: { 'Content-Type': 'application/json' }
      };
      if (data && method !== 'GET') options.body = JSON.stringify(data);

      // Handle query params for GET
      let url = '../api/handler.php?action=' + action;
      if (method === 'GET' && data) {
        for (let key in data) {
          url += `&${key}=${encodeURIComponent(data[key])}`;
        }
      }

      const resp = await fetch(url, options);
      if (!resp.ok) {
        // Log details if failure happens
        const errText = await resp.text();
        console.error('API Server Error:', errText);
        throw new Error('Server Error ' + resp.status);
      }
      return await resp.json();
    } catch (e) {
      console.error('Network/DB Error:', e);
      throw e; // Rethrow to let the UI know
    }
  },

  // --- PETS ---
  async getPets() {
    return await this.callAPI('getPets');
  },

  async getPetImages(petId) {
    return await this.callAPI('getPetImages', 'GET', { pet_id: petId });
  },

  async getCustomerSupplier(petId) {
    return await this.callAPI('getCustomerSupplier', 'GET', { pet_id: petId });
  },

  async saveCustomerSupplier(data) {
    return await this.callAPI('saveCustomerSupplier', 'POST', data);
  },

  async addPet(pet) {
    return await this.callAPI('savePet', 'POST', pet);
  },

  async toggleAlert(petId, isStopped) {
    return await this.callAPI('toggleAlert', 'POST', { id: petId, stop: isStopped });
  },

  // --- SALES ---
  async getSales() {
    return await this.callAPI('getSales');
  },

  async addSale(sale) {
    return await this.callAPI('addSale', 'POST', sale);
  },

  async getTodaySales() {
    const today = new Date().toISOString().split('T')[0];
    const all = await this.getSales();
    return all.filter(s => s.date === today);
  },

  async getSalesByPet() {
    const sales = await this.getSales();
    const petMap = {};
    sales.forEach(s => {
      petMap[s.petName] = (petMap[s.petName] || 0) + s.qty;
    });
    return Object.keys(petMap).map(name => ({ name, qty: petMap[name] }))
      .sort((a,b) => b.qty - a.qty);
  },

  // --- DRAWER ---
  async getDrawerEntries(date) {
    return await this.callAPI('getDrawer', 'GET', { date });
  },

  async saveDrawerEntries(date, payload) {
    // Pass everything in one 'data' object
    return await this.callAPI('saveDrawer', 'POST', { date, data: payload });
  }
};
