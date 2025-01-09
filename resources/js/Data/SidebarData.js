import { ref } from 'vue';

const sidebarItems = ref([
    { icon: 'pi pi-home', text: 'Home' },
    { icon: 'pi pi-search', text: 'Explore' },
    { icon: 'pi pi-envelope', text: 'Messages' },
    { icon: 'pi pi-share-alt', text: 'Relays' }
]);

export default sidebarItems;
