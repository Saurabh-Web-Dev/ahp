import axios from 'axios';

axios.defaults.baseURL = 'https://aryanhomoeopharmacy.in'; // or 127.0.0.1
axios.defaults.withCredentials = true; // VERY IMPORTANT

export default axios;
