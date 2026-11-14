' ==============================================================================
' Launcher Senyap (Silent Background) - Chatbot Service STMIK IKMI Cirebon
' Menjalankan start_service.ps1 tanpa memunculkan jendela konsol (black screen)
' ==============================================================================

Set fso = CreateObject("Scripting.FileSystemObject")
currentDir = fso.GetParentFolderName(WScript.ScriptFullName)
psScript = currentDir & "\start_service.ps1"

Set shell = CreateObject("WScript.Shell")
' WindowStyle 0 = Hidden (sama sekali tidak ada window popup)
command = "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ & psScript & """"
shell.Run command, 0, False
