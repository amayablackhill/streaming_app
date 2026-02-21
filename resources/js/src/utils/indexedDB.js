// src/utils/indexedDB.js
const DB_NAME = 'MoviesDB';
const STORE_NAME = 'movies';
const DB_VERSION = 1;

export const initDB = () => {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onerror = (event) => {
      console.error('Error opening DB', event);
      reject('Error');
    };

    request.onsuccess = (event) => {
      resolve(event.target.result);
    };

    request.onupgradeneeded = (event) => {
      const db = event.target.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        const store = db.createObjectStore(STORE_NAME, { keyPath: 'id' });
        store.createIndex('title', 'title', { unique: false });
      }
    };
  });
};

export const saveMovies = async (movies) => {
  try {
    const db = await initDB();
    const transaction = db.transaction(STORE_NAME, 'readwrite');
    const store = transaction.objectStore(STORE_NAME);

    await new Promise((resolve, reject) => {
      const clearRequest = store.clear();
      clearRequest.onsuccess = resolve;
      clearRequest.onerror = reject;
    });

    const promises = movies.map((movie) => {
      return new Promise((resolve, reject) => {
        const request = store.add(movie);
        request.onsuccess = resolve;
        request.onerror = reject;
      });
    });

    await Promise.all(promises);
    return true;
  } catch (error) {
    console.error('Error saving movies:', error);
    return false;
  }
};

export const getMovies = async () => {
  try {
    const db = await initDB();
    const transaction = db.transaction(STORE_NAME, 'readonly');
    const store = transaction.objectStore(STORE_NAME);

    return new Promise((resolve, reject) => {
      const request = store.getAll();
      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  } catch (error) {
    console.error('Error getting movies:', error);
    return [];
  }
};