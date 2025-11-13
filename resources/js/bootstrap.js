import axios from 'axios';

axios.defaults.baseURL = 'http://localhost:8000'; // or 127.0.0.1
axios.defaults.withCredentials = true; // VERY IMPORTANT

export default axios;
