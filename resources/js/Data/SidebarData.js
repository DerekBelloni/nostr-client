import { ref } from 'vue';

const sidebarItems = ref([
    { icon: 'pi pi-home', text: 'Home' },
    { icon: 'pi pi-search', text: 'Explore' },
    { icon: 'pi pi-envelope', text: 'Messages' },
    { icon: 'pi pi-share-alt', text: 'Relays' },
    { icon: 'pi pi-bookmark', text: 'Bookmarks' },
    { icon: 'pi pi-bell', text: 'Notifications' },
    { icon: 'pi pi-download', text: 'Downloads' },
    { icon: 'pi pi-cog', text: 'Settings' },
]);

export default sidebarItems;
