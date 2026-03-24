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
      if (data) options.body = JSON.stringify(data);

      const url = `../api/handler.php?action=${action}` + (method === 'GET' && data?.date ? `&date=${data.date}` : '');
      const resp = await fetch(url, options);
      if (!resp.ok) throw new Error('API Error');
      return await resp.json();
    } catch (e) {
      console.error('Database Error:', e);
      return method === 'GET' ? [] : { success: false };
    }
  },

  // --- PETS ---
  async getPets() {
    return await this.callAPI('getPets');
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
    const all = await this.getSales();
    const today = new Date().toISOString().split('T')[0];
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

  async saveDrawerEntries(date, entries) {
    return await this.callAPI('saveDrawer', 'POST', { date, data: entries });
  }
};
