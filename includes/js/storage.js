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

  async getAllCustomerSuppliers() {
    return await this.callAPI('getAllCustomerSuppliers');
  },

  async getUniqueDealers() {
    return await this.callAPI('getUniqueDealers');
  },

  async getDealerPets(dealerName) {
    return await this.callAPI('getDealerPets', 'GET', { dealer: dealerName });
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

  async markAsPaid(petId) {
    return await this.callAPI('markAsPaid', 'POST', { pet_id: petId });
  },

  async updatePayment(data) {
    return await this.callAPI('updatePayment', 'POST', data);
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
  },

  // --- AUTH ---
  async login(username, password) {
    return await this.callAPI('login', 'POST', { username, password });
  },
  async logout() {
    return await this.callAPI('logout', 'POST');
  },
  async changePassword(currentPassword, newPassword) {
    return await this.callAPI('changePassword', 'POST', { currentPassword, newPassword });
  }
};

// --- GLOBAL LANGUAGE TOGGLE (Auto-Inject) ---
document.addEventListener('DOMContentLoaded', () => {
  // 1. Create hidden Google shell
  const gte = document.createElement('div');
  gte.id = 'google_translate_element';
  gte.style.display = 'none';
  document.body.appendChild(gte);

  // 2. Create custom dropdown (ONLY ON PROFILE PAGE)
  const isProfilePage = window.location.pathname.endsWith('profile.php');
  
  if (isProfilePage) {
    const target = document.getElementById('lang-holder');
    if (target) {
        const savedLang = localStorage.getItem('app_lang') || 'en';
        target.innerHTML = `
          <select class="lang-dropdown" id="customTranslateSelector" style="width:100%; position:static;">
            <option value="en" ${savedLang === 'en' ? 'selected' : ''}>English (US)</option>
            <option value="ta" ${savedLang === 'ta' ? 'selected' : ''}>Tamil (தமிழ்)</option>
            <option value="si" ${savedLang === 'si' ? 'selected' : ''}>Sinhala (සිංහල)</option>
          </select>
        `;
        document.getElementById('customTranslateSelector').onchange = function() {
          syncTranslation(this.value);
        };
    }
  }

  window.googleTranslateElementInit = function() {
    new google.translate.TranslateElement({
      pageLanguage: 'en',
      includedLanguages: 'ta,si,en',
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
      autoDisplay: false
    }, 'google_translate_element');
  };

  const syncTranslation = (lang) => {
    localStorage.setItem('app_lang', lang);
    const cookieVal = `/en/${lang}`;
    document.cookie = `googtrans=${cookieVal}; path=/; expires=Tue, 19 Jan 2038 03:14:07 GMT`;
    document.cookie = `googtrans=${cookieVal}; path=/; domain=${window.location.hostname}; expires=Tue, 19 Jan 2038 03:14:07 GMT`;

    if (lang === 'en') {
       document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
       document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + window.location.hostname;
    }
    window.location.reload();
  }

  // Add the Google script
  const script = document.createElement('script');
  script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
  document.body.appendChild(script);

  // --- OVERKILL CLEANUP (Removes Google bar if it pops up) ---
  setInterval(() => {
    const bars = [
        document.querySelector('.goog-te-banner-frame'),
        document.querySelector('.skiptranslate'), 
        document.querySelector('.VIpgJm-ZVi9od-ORHb-O3cg3c')
    ];
    bars.forEach(b => { if(b) b.remove(); });
    document.body.style.top = '0px';
  }, 1000);
});
