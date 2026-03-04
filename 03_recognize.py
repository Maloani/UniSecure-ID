
import cv2
import os

# Charger le modèle entraîné
recognizer = cv2.face.LBPHFaceRecognizer_create()
recognizer.read('trainer/trainer.yml')

# Détecteur de visages
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")

# Détection automatique de la bonne caméra
def detect_camera_index():
    for i in range(4):
        cam = cv2.VideoCapture(i)
        if not cam.isOpened():
            continue
        ret, frame = cam.read()
        if ret:
            cam.release()
            return i
        cam.release()
    return -1

camera_index = detect_camera_index()
if camera_index == -1:
    print("[ERREUR] Aucune caméra disponible.")
    exit()
else:
    print(f"[INFO] Caméra détectée à l'index {camera_index}.")

# Initialiser la caméra
cam = cv2.VideoCapture(camera_index)
font = cv2.FONT_HERSHEY_SIMPLEX

print("\n[INFO] Démarrage de la reconnaissance faciale. Appuyez sur ESC pour quitter.\n")

while True:
    ret, frame = cam.read()
    if not ret:
        print("[ERREUR] Lecture de la caméra échouée.")
        break

    gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
    faces = face_cascade.detectMultiScale(gray, 1.2, 5)

    for (x, y, w, h) in faces:
        roi_gray = gray[y:y+h, x:x+w]
        id_pred, confidence = recognizer.predict(roi_gray)

        if confidence < 70:
            label = f"ID: {id_pred}"
            color = (0, 255, 0)  # Vert = reconnu
        else:
            label = "Inconnu"
            color = (0, 0, 255)  # Rouge = non reconnu

        cv2.rectangle(frame, (x, y), (x+w, y+h), color, 2)
        cv2.putText(frame, label, (x, y-10), font, 0.8, color, 2)

    cv2.imshow('Reconnaissance Faciale - UniSecure ID', frame)

    if cv2.waitKey(1) & 0xFF == 27:  # ESC
        break

print("\n[INFO] Fermeture de la caméra.")
cam.release()
cv2.destroyAllWindows()
