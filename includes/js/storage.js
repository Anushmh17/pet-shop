/**
 * Pet Shop Storage Manager
 * Simple persistence using localStorage
 */

const DB = {
  // --- PETS ---
  getPets: () => JSON.parse(localStorage.getItem('ps_pets')) || [
    { id: 1, name: 'Golden Retriever', category: 'dog', source: 'Dealer Supplied', type: 'Single', qty: 2, price: 15000, cost: 12000, icon: '🐶', alertLevel: 5, stopAlert: false },
    { id: 2, name: 'Persian Cat', category: 'cat', source: 'Customer Supplied', type: 'Pair/Couple', qty: 1, price: 8500, cost: 6000, icon: '🐱', alertLevel: 5, stopAlert: false },
    { id: 3, name: 'Budgerigar', category: 'bird', source: 'Dealer Supplied', type: 'Single', qty: 12, price: 500, cost: 250, icon: '🦜', alertLevel: 5, stopAlert: false },
  ],

  savePets: (pets) => localStorage.setItem('ps_pets', JSON.stringify(pets)),

  updateStock: (petId, qtyChange) => {
    const pets = DB.getPets();
    const pet = pets.find(p => p.id === petId);
    if (pet) {
      pet.qty += qtyChange;
      DB.savePets(pets);
    }
  },

  // --- SALES ---
  getSales: () => JSON.parse(localStorage.getItem('ps_sales')) || [],

  saveSales: (sales) => localStorage.setItem('ps_sales', JSON.stringify(sales)),

  addSale: (sale) => {
    const sales = DB.getSales();
    sale.id = Date.now();
    sale.date = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
    sales.unshift(sale);
    DB.saveSales(sales);
    DB.updateStock(sale.petId, -sale.qty);
  },

  getTodaySales: () => {
    const today = new Date().toISOString().split('T')[0];
    return DB.getSales().filter(s => s.date === today);
  },

  getSalesByPet: () => {
    const sales = DB.getSales();
    const petMap = {};
    sales.forEach(s => {
      petMap[s.petName] = (petMap[s.petName] || 0) + s.qty;
    });
    return Object.keys(petMap).map(name => ({ name, qty: petMap[name] }))
      .sort((a,b) => b.qty - a.qty);
  },

  // --- DRAWER ---
  getDrawerEntries: (date) => {
    const allEntries = JSON.parse(localStorage.getItem('ps_drawer')) || {};
    return allEntries[date] || [];
  },

  saveDrawerEntries: (date, entries) => {
    const allEntries = JSON.parse(localStorage.getItem('ps_drawer')) || {};
    allEntries[date] = entries;
    localStorage.setItem('ps_drawer', JSON.stringify(allEntries));
  }
};
