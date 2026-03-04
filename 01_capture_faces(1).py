
import cv2
import os

face_detector = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")
cam = cv2.VideoCapture(1)  # Caméra fonctionnelle détectée à l'index 1
cam.set(3, 640)
cam.set(4, 480)

if not cam.isOpened():
    print("[ERREUR] Impossible d’ouvrir la webcam.")
    exit()

face_id = input('\nEntrez un ID numérique pour la personne (ex : 1) et appuyez sur Entrée : ')
count = 0

os.makedirs(f'dataset/{face_id}', exist_ok=True)

print("\n[INFO] Capture commencée. Regardez la caméra.")
print("[INFO] Appuyez sur ESC pour arrêter ou attendre la capture de 30 images.\n")

while True:
    ret, frame = cam.read()
    if not ret:
        print("[ERREUR] Échec de la capture de la caméra.")
        break

    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    faces = face_detector.detectMultiScale(gray, 1.3, 5)

    for (x, y, w, h) in faces:
        count += 1
        face_img = gray[y:y+h, x:x+w]
        cv2.imwrite(f"dataset/{face_id}/{count}.jpg", face_img)

        cv2.rectangle(frame, (x, y), (x+w, y+h), (0, 255, 0), 2)
        cv2.putText(frame, f"Image {count}/30", (x, y-10), cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)

    cv2.imshow('Capture Visage - UniSecure ID', frame)

    k = cv2.waitKey(1) & 0xFF
    if k == 27 or count >= 30:
        break

print(f"\n[INFO] Capture terminée pour l’ID {face_id}. {count} images enregistrées.")
cam.release()
cv2.destroyAllWindows()
