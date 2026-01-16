import axios from 'axios';
import Cookies from 'js-cookie';
import toastr from 'toastr';
import { history } from '../helpers/routing';

toastr.options = {
  positionClass: 'toast-bottom-left',
  hideDuration: 300,
  timeOut: 10000,
};

axios.interceptors.request.use(
  (config) => {
    const token = Cookies.get('token');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

axios.interceptors.response.use(
  (response) => {
    const { code } = response.data;
    if (response.status == 200) {
      switch (code) {
        case 403:
          return history.push('/403');
        case 503:
          return history.push('/503');
        default:
          return response;
      }
    }
  },
  (error) => {
    toastr.clear();
    setTimeout(() => toastr.error('Something went wrong'), 300);
  },
);

export default axios;
