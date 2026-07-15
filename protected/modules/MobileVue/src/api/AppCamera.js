import { Camera, CameraResultType, CameraSource } from "@capacitor/camera";
import { Filesystem, Directory, Encoding } from "@capacitor/filesystem";

const AppCamera = {
  async isCameraEnabled() {
    let myPromise = new Promise(function (resolve, reject) {
      Camera.checkPermissions()
        .then((data) => {
          switch (data.camera) {
            case "prompt":
            case "prompt-with-rationale":
            case "denied":
              Camera.requestPermissions()
                .then((data) => {
                  switch (data.camera) {
                    case "granted":
                      resolve("granted");
                      break;
                    default:
                      reject("denied");
                      break;
                  }
                })
                .catch((error) => {
                  reject(error);
                });
              break;
            case "granted":
              resolve("granted");
              break;
          }
        })
        .catch((error) => {
          reject(error);
        });
    });
    return await myPromise;
  },
  async isFileAccessEnabled() {
    let myPromise = new Promise(function (resolve, reject) {
      Filesystem.checkPermissions()
        .then((data) => {
          switch (data.publicStorage) {
            case "prompt":
            case "prompt-with-rationale":
            case "denied":
              Filesystem.requestPermissions()
                .then((data) => {
                  switch (data.publicStorage) {
                    case "granted":
                      resolve("granted");
                      break;
                    default:
                      reject("denied");
                      break;
                  }
                })
                .catch((error) => {
                  reject(error);
                });
              break;

            case "granted":
              resolve("granted");
              break;
            default:
              reject("denied");
              break;
          }
        })
        .catch((error) => {
          reject(error);
        });
    });
    return await myPromise;
  },
  async getPhoto(sourceType) {
    let $source = CameraSource.Prompt;
    if (sourceType == 2) {
      $source = CameraSource.Camera;
    } else if (sourceType == 3) {
      $source = CameraSource.Photos;
    }
    const image = await Camera.getPhoto({
      quality: 90,
      allowEditing: false,
      resultType: CameraResultType.Uri,
      width: 500,
      source: $source,
    });
    if (image) {
      const contents = await Filesystem.readFile({
        path: image.path,
      });
      if (contents) {
        return {
          format: image.format,
          path: image.webPath,
          data: contents.data,
        };
      }
    }
    return false;
  },
  async ReadFile(imagePath) {
    const contents = await Filesystem.readFile({
      path: imagePath,
    });
    if (contents) {
      return contents;
    }
    return false;
  },
  //
};
export default AppCamera;
